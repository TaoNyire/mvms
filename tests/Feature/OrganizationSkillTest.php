<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Skill;

class OrganizationSkillTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'organization']);
        Role::create(['name' => 'volunteer']);
    }

    /**
     * Test organization can create custom skills
     */
    public function test_organization_can_create_custom_skill(): void
    {
        // Create organization user
        $organization = User::factory()->create();
        $orgRole = Role::where('name', 'organization')->first();
        $organization->roles()->attach($orgRole);

        // Create a custom skill
        $response = $this->actingAs($organization, 'sanctum')
            ->postJson('/api/organization/skills', [
                'name' => 'Custom Marketing Skill',
                'description' => 'Specific marketing skill for our organization',
                'category' => 'custom',
                'required_proficiency_level' => 'intermediate',
                'priority' => 5,
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id',
            'name',
            'description',
            'category',
            'skill_type',
            'organization_id',
            'required_proficiency_level',
            'priority'
        ]);

        // Verify skill was created in database
        $this->assertDatabaseHas('skills', [
            'name' => 'Custom Marketing Skill',
            'organization_id' => $organization->id,
            'skill_type' => 'organization_specific'
        ]);
    }

    /**
     * Test organization can view all available skills
     */
    public function test_organization_can_view_all_available_skills(): void
    {
        // Create organization user
        $organization = User::factory()->create();
        $orgRole = Role::where('name', 'organization')->first();
        $organization->roles()->attach($orgRole);

        // Create a global skill
        $globalSkill = Skill::create([
            'name' => 'Global Communication',
            'category' => 'communication',
            'skill_type' => 'global',
            'is_active' => true
        ]);

        // Create an organization-specific skill
        $orgSkill = Skill::create([
            'name' => 'Organization Specific Skill',
            'category' => 'custom',
            'skill_type' => 'organization_specific',
            'organization_id' => $organization->id,
            'is_active' => true
        ]);

        $response = $this->actingAs($organization, 'sanctum')
            ->getJson('/api/organization/skills');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'skills',
            'global_skills',
            'organization_skills'
        ]);

        // Should include both global and organization skills
        $skills = $response->json('skills');
        $this->assertCount(2, $skills);
    }

    /**
     * Test organization cannot create duplicate skill names
     */
    public function test_organization_cannot_create_duplicate_skill_names(): void
    {
        // Create organization user
        $organization = User::factory()->create();
        $orgRole = Role::where('name', 'organization')->first();
        $organization->roles()->attach($orgRole);

        // Create first skill
        Skill::create([
            'name' => 'Duplicate Skill',
            'category' => 'custom',
            'skill_type' => 'organization_specific',
            'organization_id' => $organization->id,
            'is_active' => true
        ]);

        // Try to create duplicate
        $response = $this->actingAs($organization, 'sanctum')
            ->postJson('/api/organization/skills', [
                'name' => 'Duplicate Skill',
                'description' => 'This should fail',
                'category' => 'custom',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    /**
     * Test organization can update their own skills
     */
    public function test_organization_can_update_own_skills(): void
    {
        // Create organization user
        $organization = User::factory()->create();
        $orgRole = Role::where('name', 'organization')->first();
        $organization->roles()->attach($orgRole);

        // Create organization skill
        $skill = Skill::create([
            'name' => 'Original Skill',
            'category' => 'custom',
            'skill_type' => 'organization_specific',
            'organization_id' => $organization->id,
            'is_active' => true
        ]);

        // Update the skill
        $response = $this->actingAs($organization, 'sanctum')
            ->putJson("/api/organization/skills/{$skill->id}", [
                'name' => 'Updated Skill',
                'description' => 'Updated description',
                'priority' => 10,
            ]);

        $response->assertStatus(200);

        // Verify update in database
        $this->assertDatabaseHas('skills', [
            'id' => $skill->id,
            'name' => 'Updated Skill',
            'description' => 'Updated description',
            'priority' => 10
        ]);
    }

    /**
     * Test organization can delete their own skills
     */
    public function test_organization_can_delete_own_skills(): void
    {
        // Create organization user
        $organization = User::factory()->create();
        $orgRole = Role::where('name', 'organization')->first();
        $organization->roles()->attach($orgRole);

        // Create organization skill
        $skill = Skill::create([
            'name' => 'Skill to Delete',
            'category' => 'custom',
            'skill_type' => 'organization_specific',
            'organization_id' => $organization->id,
            'is_active' => true
        ]);

        // Delete the skill
        $response = $this->actingAs($organization, 'sanctum')
            ->deleteJson("/api/organization/skills/{$skill->id}");

        $response->assertStatus(200);

        // Verify deletion
        $this->assertDatabaseMissing('skills', [
            'id' => $skill->id
        ]);
    }
}
