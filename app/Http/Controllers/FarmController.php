<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Farm;
use App\Models\FarmInvite;
use App\Models\Province;
use App\Models\City;
use App\Http\Requests\StoreFarmRequest;
use App\Http\Requests\UpdateFarmRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Traits\HasCurrentFarm;
use App\Services\FileUploadService;

class FarmController extends Controller
{
    use HasCurrentFarm;

    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        $farm = $this->hasCurrentFarm() ? $this->getCurrentFarm() : null;

        return Inertia::render('farms/Form', [
            'provinces' => Province::all(),
            'cities' => City::all(),
            'user' => $user,
            'farm' => $farm,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFarmRequest $request)
    {
        $data = $request->validated();
        $user = auth()->user();

        if ($request->hasFile('picture_blob')) {
            $data['picture'] = $this->fileUploadService->uploadImage(
                $request->file('picture_blob'),
                'farm_pictures'
            );
        }
        $farm = Farm::create([
            ...$data,
            'user_id' => auth()->id(),
        ]);

        $user->update(['current_farm_id' => $farm->id]);
        $user->farms()->attach($farm->id, ['role' => 'owner']);
        // $user->assignFarmRole('owner');
        
        // Create default medicine inventory items for the new farm
        $farm->createDefaultMedicines();

        return redirect()->route('farms.show', ['farm' => $farm->id])
            ->with('success', 'Farm created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Farm $farm)
    {
        $farm = Farm::findOrFail($this->getCurrentFarmId());
        $farm->load(['users']);
        return Inertia::render('farms/Show', [
            'farm' => $farm ?? null,
            'invites' => FarmInvite::where('farm_id', $farm->id)->get(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Farm $farm)
    {
        $user = auth()->user();
        // Use the farm from route model binding, but load the current farm's data
        $currentFarm = Farm::findOrFail($this->getCurrentFarmId());
        $currentFarm->load(['city.province']);
        
        return Inertia::render('farms/Form', [
            'provinces' => Province::all(),
            'cities' => City::all(),
            'user' => $user,
            'farm' => $currentFarm,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFarmRequest $request, Farm $farm)
    {
        $data = $request->validated();

        // Check if a new picture was uploaded
        if ($request->hasFile('picture_blob')) {
            // Extract old picture path for deletion
            $oldPicture = $farm->picture;
            $oldPath = null;
            
            if ($oldPicture) {
                // Extract path from full URL if it contains storage/
                if (str_contains($oldPicture, '/storage/')) {
                    $oldPath = str_replace([asset(''), '/storage/'], '', $oldPicture);
                    $oldPath = ltrim($oldPath, '/');
                }
            }

            // Upload new picture
            $data['picture'] = $this->fileUploadService->uploadImage(
                $request->file('picture_blob'),
                'farm_pictures'
            );

            // Delete old picture if it exists
            if ($oldPath) {
                $this->fileUploadService->deleteFile($oldPath);
            }
        }

        $farm->update($data);
        return redirect()->back()->with('success', 'Data peternakan berhasil diperbarui.');
    }
    
    public function updateRole(Request $request, Farm $farm, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|string|max:50',
        ]);

        // Check if the user is actually related to this farm
        if (!$farm->users()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Anda tidak memiliki akses ke peternakan ini.');
        }

        // Update the pivot table
        $farm->users()->updateExistingPivot($user->id, ['role' => $validated['role']]);

        return back()->with('success', 'Role updated!');
    }
    
    public function updateRoleInvite(Request $request, Farm $farm, $email)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:farm_invites,email,farm_id,' . $farm->id,
            'role' => 'required|string|max:50',
        ]);

        FarmInvite::where('farm_id', $farm->id)
            ->where('email', $email)
            ->update(['role' => $validated['role']]);

        return back()->with('success', 'Role updated!');
    }
    
    public function updateUserProfile(Request $request, Farm $farm, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
            'email' => 'sometimes|required|email|max:255|unique:users,email,' . $user->id,
        ]);

        // Check if the user is actually related to this farm
        if (!$farm->users()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Anda tidak memiliki akses ke peternakan ini.');
        }

        // Check if current user has permission (admin or owner)
        $currentUserRole = $farm->users()->where('user_id', auth()->id())->first()->pivot->role;
        if (!in_array($currentUserRole, ['admin', 'owner'])) {
            return back()->with('error', 'Anda tidak memiliki izin untuk mengubah data anggota.');
        }

        // Prevent editing owner profile by non-owners
        $targetUserRole = $farm->users()->where('user_id', $user->id)->first()->pivot->role;
        if ($targetUserRole === 'owner' && $currentUserRole !== 'owner') {
            return back()->with('error', 'Anda tidak dapat mengubah data pemilik peternakan.');
        }

        // Update user profile
        $user->update($validated);

        return back()->with('success', 'Data anggota berhasil diubah!');
    }
    
    public function switch(Request $request, Farm $farm)
    {
        $user = auth()->user();

        // Check if the farm belongs to the user (skip this check for super users)
        if (!$user->is_super_user && !$user->farms()->where('farms.id', $farm->id)->exists()) {
            return redirect()->route('farms.index')->with('error', 'Anda tidak memiliki akses ke peternakan ini.');
        }

        $previousFarm = $user->currentFarm;

        // Update the user's current farm
        $user->update(['current_farm_id' => $farm->id]);

        // Send Telegram notification
        $this->sendFarmSwitchNotification($user, $farm, $previousFarm);

        $currentRoute = $request->route()->getName();

       return redirect()->route('dashboard')->with('success', 'Anda telah beralih ke peternakan: ' . $farm->name);
    }

    private function sendFarmSwitchNotification($user, $newFarm, $previousFarm = null): void
    {
        try {
            $message = "Pengguna beralih farm:\n";
            if ($previousFarm) {
                $message .= "• Dari: {$previousFarm->name}\n";
            }
            $message .= "• Ke: {$newFarm->name}";
            if ($newFarm->owner) {
                $message .= "\n• Pemilik: {$newFarm->owner}";
            }

            $notifiable = new class {
                public function routeNotificationForTelegram() {
                    return null;
                }
            };

            \Illuminate\Support\Facades\Notification::send($notifiable, new \App\Notifications\GeneralTelegramNotification([
                'type' => 'info',
                'title' => '🔄 Beralih Farm',
                'message' => $message,
                'farm_name' => $newFarm->name,
                'created_by' => $user->name,
                'action_url' => url('/dashboard'),
            ]));

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send farm switch Telegram notification: ' . $e->getMessage());
        }
    }
    
    public function inviteMember(Request $request, Farm $farm)
    {
        $request->validate([
            'email' => 'required|email|unique:farm_invites,email,NULL,id,farm_id,' . $farm->id,
            'role' => 'required|string|max:50',
        ]);

        FarmInvite::create([
            'farm_id' => $farm->id,
            'email' => $request->email,
            'role' => $request->role,
        ]);
       return redirect()->back()->with('success', 'Pengguna dengan email ' . $request->email . ' telah diundang ke peternakan: ' . $farm->name);
    }
    
    public function destroyMember(Request $request, Farm $farm, User $user)
    {
        // Check if the user is actually related to this farm
        if (!$farm->users()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Anda tidak memiliki akses ke peternakan ini.');
        }

        // Remove the user from the farm
        $farm->users()->detach($user->id);
        $user->update(['current_farm_id' => null]);

        return back()->with('success', 'Pengguna dengan ID ' . $user->id . ' telah dihapus dari peternakan: ' . $farm->name);
    }
    
    public function destroyMemberInvite(Request $request, Farm $farm, string $email)
    {
        // Check if the invite exists
        $invite = FarmInvite::where('farm_id', $farm->id)
            ->where('email', $email)
            ->first();

        if (!$invite) {
            return back()->with('error', 'Undangan tidak ditemukan untuk email: ' . $email);
        }

        // Delete the invite
        $invite->delete();

        return back()->with('success', 'Undangan untuk email ' . $email . ' telah dihapus dari peternakan: ' . $farm->name);
    }

    public function acceptInvite(FarmInvite $invite)
    {
        $user = auth()->user();
        
        // Check if the invite is for the current user
        if ($invite->email !== $user->email) {
            return back()->with('error', 'Undangan ini bukan untuk Anda.');
        }
        
        // Check if user is already a member of this farm
        if ($user->farms()->where('farms.id', $invite->farm_id)->exists()) {
            $invite->delete();
            return back()->with('error', 'Anda sudah menjadi anggota peternakan ini.');
        }
        
        // Add user to farm
        $user->farms()->attach($invite->farm_id, ['role' => $invite->role]);
        
        // Delete the invitation
        $invite->delete();
        
        return redirect()->route('dashboard')->with('success', 'Anda telah bergabung dengan peternakan: ' . $invite->farm->name);
    }
    
    public function rejectInvite(FarmInvite $invite)
    {
        $user = auth()->user();
        
        // Check if the invite is for the current user
        if ($invite->email !== $user->email) {
            return back()->with('error', 'Undangan ini bukan untuk Anda.');
        }
        
        // Delete the invitation
        $invite->delete();
        
        return back()->with('success', 'Undangan telah ditolak.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Farm $farm)
    {
        //
    }
}
