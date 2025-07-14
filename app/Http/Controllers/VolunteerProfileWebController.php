<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\VolunteerProfile;

class VolunteerProfileWebController extends Controller
{
    /**
     * Show the profile creation form
     */
    public function create()
    {
        $user = Auth::user();

        // Check if user already has a profile
        $profile = $user->volunteerProfile;

        if ($profile && $profile->is_complete) {
            return redirect()->route('volunteer.dashboard')
                ->with('info', 'Your profile is already complete.');
        }

        return view('volunteer.profile.create', compact('profile'));
    }

    /**
     * Show the profile (for viewing)
     */
    public function show()
    {
        $user = Auth::user();
        $profile = $user->volunteerProfile;

        if (!$profile) {
            return redirect()->route('volunteer.profile.create');
        }

        return view('volunteer.profile.show', compact('profile'));
    }

    /**
     * Show the profile edit form
     */
    public function edit()
    {
        $user = Auth::user();
        $profile = $user->volunteerProfile;

        if (!$profile) {
            return redirect()->route('volunteer.profile.create');
        }

        return view('volunteer.profile.edit', compact('profile'));
    }

    /**
     * Store the volunteer profile
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Debug: Log incoming request data
        \Log::info('Incoming request data:', $request->all());

        // Validation rules - more lenient for better user experience
        $rules = [
            'full_name' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            'phone' => 'nullable|string|max:20',
            'alternative_phone' => 'nullable|string|max:20',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string',
            'physical_address' => 'nullable|string',
            'district' => 'nullable|string',
            'region' => 'nullable|string',
            'postal_code' => 'nullable|string|max:10',
            'skills' => 'nullable|array',
            'experience_description' => 'nullable|string',
            'education_level' => 'nullable|string',
            'field_of_study' => 'nullable|string|max:255',
            'available_days' => 'nullable|array',
            'available_time_start' => 'nullable|date_format:H:i',
            'available_time_end' => 'nullable|date_format:H:i',
            'availability_type' => 'nullable|in:full_time,part_time,weekends,flexible',
            'can_travel' => 'nullable|in:0,1,true,false',
            'max_travel_distance' => 'nullable|integer|max:1000',
            'motivation' => 'nullable|string',
            'has_volunteered_before' => 'nullable|boolean',
            'previous_volunteer_experience' => 'nullable|string',
            'id_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'cv_document' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'certificates.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];

        $validatedData = $request->validate($rules);

        // Handle boolean fields properly
        $validatedData['can_travel'] = $request->has('can_travel') && in_array($request->input('can_travel'), ['1', 'true', true], true);
        $validatedData['has_volunteered_before'] = $request->has('has_volunteered_before') && in_array($request->input('has_volunteered_before'), ['1', 'true', true], true);

        // Remove max_travel_distance if can_travel is false
        if (!$validatedData['can_travel']) {
            unset($validatedData['max_travel_distance']);
        }

        // Handle file uploads
        $documentPaths = $this->handleFileUploads($request);

        // Merge file paths with validated data
        $profileData = array_merge($validatedData, $documentPaths);

        // Debug: Log the data being saved
        \Log::info('Profile data being saved:', $profileData);

        // Create or update profile
        $profile = VolunteerProfile::updateOrCreate(
            ['user_id' => $user->id],
            $profileData
        );

        // Mark profile as complete if it meets requirements
        if ($profile->completion_percentage >= 80) {
            $profile->markAsComplete();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile saved successfully!',
                'profile' => $profile,
                'completion_percentage' => $profile->completion_percentage
            ]);
        }

        return redirect()->route('volunteer.dashboard')
            ->with('success', 'Profile completed successfully! You can now apply for volunteer opportunities.');
    }

    /**
     * Handle file uploads
     */
    private function handleFileUploads(Request $request)
    {
        $filePaths = [];

        // Handle ID document upload
        if ($request->hasFile('id_document')) {
            $file = $request->file('id_document');
            $path = $file->store('volunteer-documents/id', 'public');
            $filePaths['id_document_path'] = $path;
            $filePaths['id_document_original_name'] = $file->getClientOriginalName();
        }

        // Handle CV upload
        if ($request->hasFile('cv_document')) {
            $file = $request->file('cv_document');
            $path = $file->store('volunteer-documents/cv', 'public');
            $filePaths['cv_path'] = $path;
            $filePaths['cv_original_name'] = $file->getClientOriginalName();
        }

        // Handle certificates upload
        if ($request->hasFile('certificates')) {
            $certificatePaths = [];
            foreach ($request->file('certificates') as $file) {
                $path = $file->store('volunteer-documents/certificates', 'public');
                $certificatePaths[] = [
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName()
                ];
            }
            $filePaths['certificates'] = $certificatePaths;
        }

        return $filePaths;
    }
}
