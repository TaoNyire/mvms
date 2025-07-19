<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\OrganizationProfile;

class OrganizationProfileWebController extends Controller
{
    /**
     * Show the profile creation/edit form
     */
    public function create()
    {
        $user = Auth::user();

        // Get existing profile for editing or create new
        $profile = $user->organizationProfile;

        return view('organization.profile.create', compact('profile'));
    }

    /**
     * Show the profile (for editing)
     */
    public function show()
    {
        $user = Auth::user();
        $profile = $user->organizationProfile;

        if (!$profile) {
            return redirect()->route('organization.profile.create');
        }

        return view('organization.profile.show', compact('profile'));
    }

    /**
     * Store the organization profile
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            // Debug: Log incoming request data
            \Log::info('Organization profile request data:', $request->all());

        // Validation rules - lenient for better user experience
        $rules = [
            'org_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'mission' => 'nullable|string',
            'vision' => 'nullable|string',
            'sector' => 'nullable|string',
            'org_type' => 'nullable|string',
            'registration_number' => 'nullable|string|max:100',
            'is_registered' => 'nullable|in:0,1,true,false,on',
            'registration_date' => 'nullable|date|before_or_equal:today',
            'registration_authority' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:100',
            'physical_address' => 'nullable|string',
            'district' => 'nullable|string',
            'region' => 'nullable|string',
            'postal_address' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'alternative_phone' => 'nullable|string|max:20',
            'website' => 'nullable|string|max:255',
            'social_media' => 'nullable|string',
            'focus_areas' => 'nullable|array',
            'target_beneficiaries' => 'nullable|array',
            'geographical_coverage' => 'nullable|array',
            'staff_count' => 'nullable|integer',
            'volunteer_count' => 'nullable|integer',
            'annual_budget' => 'nullable|numeric',
            'established_date' => 'nullable|date|before:today',
            'services_offered' => 'nullable|array',
            'resources_available' => 'nullable|array',
            'partnerships' => 'nullable|array',
            'achievements' => 'nullable|string',
            'current_projects' => 'nullable|string',
            'contact_person_name' => 'nullable|string|max:255',
            'contact_person_title' => 'nullable|string|max:255',
            'contact_person_phone' => 'nullable|string|max:20',
            'contact_person_email' => 'nullable|email|max:255',
            'additional_info' => 'nullable|string',
            'volunteer_requirements' => 'nullable|string',
            'registration_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'tax_clearance' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'other_documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ];

        $validatedData = $request->validate($rules);

        // Handle boolean fields properly
        $validatedData['is_registered'] = $request->has('is_registered') && in_array($request->input('is_registered'), ['1', 'true', true, 'on'], true);

        // Handle website URL - add protocol if missing and validate
        if (!empty($validatedData['website'])) {
            $website = $validatedData['website'];
            if (!preg_match('/^https?:\/\//', $website)) {
                $website = 'https://' . $website;
            }

            // Validate the URL after adding protocol
            if (!filter_var($website, FILTER_VALIDATE_URL)) {
                return back()->withErrors(['website' => 'Please enter a valid website URL.'])->withInput();
            }

            $validatedData['website'] = $website;
        }

        // Handle file uploads
        $documentPaths = $this->handleFileUploads($request);

        // Merge file paths with validated data
        $profileData = array_merge($validatedData, $documentPaths);

        // Debug: Log the data being saved
        \Log::info('Organization profile data being saved:', $profileData);

        // Create or update profile
        $profile = OrganizationProfile::updateOrCreate(
            ['user_id' => $user->id],
            $profileData
        );

        // Mark profile as complete if it meets requirements
        if ($profile->completion_percentage >= 85) {
            $profile->markAsComplete();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Organization profile saved successfully!',
                'profile' => $profile,
                'completion_percentage' => $profile->completion_percentage
            ]);
        }

        return redirect()->route('organization.dashboard')
            ->with('success', 'Organization profile completed successfully! Your profile is now under review.');

        } catch (\Exception $e) {
            \Log::error('Organization profile creation error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while saving your profile. Please try again.'
                ], 500);
            }

            return back()->withErrors(['error' => 'An error occurred while saving your profile. Please try again.'])->withInput();
        }
    }

    /**
     * Handle file uploads
     */
    private function handleFileUploads(Request $request)
    {
        $filePaths = [];

        // Handle registration certificate upload
        if ($request->hasFile('registration_certificate')) {
            $file = $request->file('registration_certificate');
            $path = $file->store('organization-documents/certificates', 'public');
            $filePaths['registration_certificate_path'] = $path;
            $filePaths['registration_certificate_original_name'] = $file->getClientOriginalName();
        }

        // Handle tax clearance upload
        if ($request->hasFile('tax_clearance')) {
            $file = $request->file('tax_clearance');
            $path = $file->store('organization-documents/tax', 'public');
            $filePaths['tax_clearance_path'] = $path;
            $filePaths['tax_clearance_original_name'] = $file->getClientOriginalName();
        }

        // Handle other documents upload
        if ($request->hasFile('other_documents')) {
            $otherDocuments = [];
            foreach ($request->file('other_documents') as $file) {
                $path = $file->store('organization-documents/other', 'public');
                $otherDocuments[] = [
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName()
                ];
            }
            $filePaths['other_documents'] = $otherDocuments;
        }

        return $filePaths;
    }
}
