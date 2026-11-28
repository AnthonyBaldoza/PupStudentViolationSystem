<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use App\Models\ViolationReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ProfileController extends Controller
{
    /** The logged-in user's own profile. */
    public function edit(Request $request): View
    {
        $user = $request->user();

        // Real numbers from the database, different for each role.
        if ($user->isOfficer()) {
            $byStatus = $user->submittedReports()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $stats = [
                ['Submitted', $byStatus->sum()],
                ['Approved', $byStatus->get(ViolationReport::STATUS_APPROVED, 0)],
                ['Pending', $byStatus->get(ViolationReport::STATUS_PENDING, 0)],
                ['Rejected', $byStatus->get(ViolationReport::STATUS_REJECTED, 0)],
            ];
        } else {
            $byStatus = $user->reviewedReports()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $stats = [
                ['Reviewed', $byStatus->sum()],
                ['Approved', $byStatus->get(ViolationReport::STATUS_APPROVED, 0)],
                ['Rejected', $byStatus->get(ViolationReport::STATUS_REJECTED, 0)],
                ['Waiting for review', ViolationReport::pending()->count()],
            ];
        }

        return view('profile.edit', compact('user', 'stats'));
    }

    /** Name, email, details and profile picture. Role can never be changed here. */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->fill($request->safe()->only(['name', 'email']));

        // Detail columns are deliberately not mass-assignable on the model, so they are
        // set with forceFill, and only the fields that belong to this user's role.
        $user->forceFill($request->safe()->only(['contact_number', 'position']));

        if ($user->isOfficer()) {
            $user->forceFill($request->safe()->only(['student_number', 'course', 'year_level', 'section']));
        } else {
            $user->forceFill([
                'department' => $request->validated('department'),
                // Unchecked boxes are simply absent from the request, so default to none.
                'handled_courses' => array_values($request->validated('handled_courses', [])),
                'handled_year_levels' => array_values($request->validated('handled_year_levels', [])),
            ]);
        }

        $oldPath = $user->photo_path;
        $newPath = null;
        $photoChanged = false;

        if ($request->hasFile('photo')) {
            // Private disk, random file name.
            $newPath = $request->file('photo')->store('profile-photos', 'local');
            $user->photo_path = $newPath;
            $photoChanged = true;
        } elseif ($request->boolean('remove_photo') && $oldPath) {
            $user->photo_path = null;
            $photoChanged = true;
        }

        try {
            $user->save();
        } catch (Throwable $e) {
            // Don't leave an orphan file behind if the database update failed.
            if ($newPath) {
                Storage::disk('local')->delete($newPath);
            }
            throw $e;
        }

        if ($photoChanged && $oldPath) {
            Storage::disk('local')->delete($oldPath); // remove the replaced picture
        }

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Profile updated.');
    }

    /** Change own password. Requires the current password. */
    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'confirmed',
                'different:current_password',
                Password::min(8)->letters()->numbers(),
            ],
        ], [
            'current_password.current_password' => 'The current password is incorrect.',
            'password.different' => 'The new password must be different from the current one.',
        ]);

        $request->user()->update(['password' => $data['password']]); // hashed by the model cast

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Password changed.');
    }

    /**
     * Profile picture. A person can open their own; an admin can open anyone's.
     * The URL carries a version (?v=) that changes with the user's last update,
     * so it is safe for the browser to keep it for a day.
     */
    public function photo(Request $request, User $user): StreamedResponse
    {
        $viewer = $request->user();

        abort_unless($viewer->isAdmin() || (int) $viewer->id === (int) $user->id, 403);

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        abort_if(
            ! $user->photo_path || ! $disk->exists($user->photo_path),
            404,
            'No profile picture.'
        );

        return $disk->response(
            $user->photo_path,
            null,
            [
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, max-age=86400',
            ]
        );
    }
}