<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Skill;

class SkillSeeder extends Seeder
{
    public function run()
    {
        $skills = [
            // Technical Skills
            ['name' => 'Web Development', 'category' => 'technical', 'description' => 'Building and maintaining websites'],
            ['name' => 'Database Management', 'category' => 'technical', 'description' => 'Managing and organizing databases'],
            ['name' => 'Graphic Design', 'category' => 'technical', 'description' => 'Creating visual content and designs'],
            ['name' => 'Video Editing', 'category' => 'technical', 'description' => 'Editing and producing video content'],
            ['name' => 'Photography', 'category' => 'technical', 'description' => 'Taking and editing photographs'],
            ['name' => 'Social Media Management', 'category' => 'technical', 'description' => 'Managing social media accounts and content'],
            ['name' => 'Data Analysis', 'category' => 'technical', 'description' => 'Analyzing and interpreting data'],
            ['name' => 'Microsoft Office', 'category' => 'technical', 'description' => 'Proficiency in Microsoft Office suite'],
            ['name' => 'Content Writing', 'category' => 'technical', 'description' => 'Creating written content for various purposes'],
            
            // Communication Skills
            ['name' => 'Public Speaking', 'category' => 'communication', 'description' => 'Speaking effectively to groups'],
            ['name' => 'Written Communication', 'category' => 'communication', 'description' => 'Clear and effective writing'],
            ['name' => 'Presentation Skills', 'category' => 'communication', 'description' => 'Creating and delivering presentations'],
            ['name' => 'Active Listening', 'category' => 'communication', 'description' => 'Listening effectively and empathetically'],
            ['name' => 'Conflict Resolution', 'category' => 'communication', 'description' => 'Resolving disputes and conflicts'],
            ['name' => 'Negotiation', 'category' => 'communication', 'description' => 'Negotiating agreements and solutions'],
            
            // Leadership Skills
            ['name' => 'Team Leadership', 'category' => 'leadership', 'description' => 'Leading and managing teams'],
            ['name' => 'Project Management', 'category' => 'leadership', 'description' => 'Planning and executing projects'],
            ['name' => 'Mentoring', 'category' => 'leadership', 'description' => 'Guiding and developing others'],
            ['name' => 'Strategic Planning', 'category' => 'leadership', 'description' => 'Developing long-term strategies'],
            ['name' => 'Decision Making', 'category' => 'leadership', 'description' => 'Making effective decisions'],
            ['name' => 'Delegation', 'category' => 'leadership', 'description' => 'Assigning tasks effectively'],
            
            // Creative Skills
            ['name' => 'Creative Writing', 'category' => 'creative', 'description' => 'Writing creative content and stories'],
            ['name' => 'Art and Illustration', 'category' => 'creative', 'description' => 'Creating visual art and illustrations'],
            ['name' => 'Music Performance', 'category' => 'creative', 'description' => 'Performing music'],
            ['name' => 'Event Planning', 'category' => 'creative', 'description' => 'Planning and organizing events'],
            ['name' => 'Interior Design', 'category' => 'creative', 'description' => 'Designing interior spaces'],
            ['name' => 'Crafts and DIY', 'category' => 'creative', 'description' => 'Creating handmade items and crafts'],
            
            // Analytical Skills
            ['name' => 'Research', 'category' => 'analytical', 'description' => 'Conducting thorough research'],
            ['name' => 'Problem Solving', 'category' => 'analytical', 'description' => 'Identifying and solving problems'],
            ['name' => 'Critical Thinking', 'category' => 'analytical', 'description' => 'Analyzing information critically'],
            ['name' => 'Financial Analysis', 'category' => 'analytical', 'description' => 'Analyzing financial data'],
            ['name' => 'Statistical Analysis', 'category' => 'analytical', 'description' => 'Working with statistics and data'],
            
            // Interpersonal Skills
            ['name' => 'Customer Service', 'category' => 'interpersonal', 'description' => 'Providing excellent customer service'],
            ['name' => 'Counseling', 'category' => 'interpersonal', 'description' => 'Providing guidance and support'],
            ['name' => 'Teaching', 'category' => 'interpersonal', 'description' => 'Teaching and training others'],
            ['name' => 'Networking', 'category' => 'interpersonal', 'description' => 'Building professional relationships'],
            ['name' => 'Cultural Sensitivity', 'category' => 'interpersonal', 'description' => 'Working effectively across cultures'],
            ['name' => 'Empathy', 'category' => 'interpersonal', 'description' => 'Understanding and sharing feelings of others'],
            
            // Organizational Skills
            ['name' => 'Time Management', 'category' => 'organizational', 'description' => 'Managing time effectively'],
            ['name' => 'Administrative Support', 'category' => 'organizational', 'description' => 'Providing administrative assistance'],
            ['name' => 'Record Keeping', 'category' => 'organizational', 'description' => 'Maintaining accurate records'],
            ['name' => 'Scheduling', 'category' => 'organizational', 'description' => 'Organizing schedules and appointments'],
            ['name' => 'Inventory Management', 'category' => 'organizational', 'description' => 'Managing inventory and supplies'],
            ['name' => 'Budget Management', 'category' => 'organizational', 'description' => 'Managing budgets and finances'],
            
            // Physical Skills
            ['name' => 'Manual Labor', 'category' => 'physical', 'description' => 'Physical work and labor'],
            ['name' => 'Construction', 'category' => 'physical', 'description' => 'Building and construction work'],
            ['name' => 'Gardening', 'category' => 'physical', 'description' => 'Gardening and landscaping'],
            ['name' => 'Cooking', 'category' => 'physical', 'description' => 'Preparing and cooking food'],
            ['name' => 'Cleaning', 'category' => 'physical', 'description' => 'Cleaning and maintenance'],
            ['name' => 'Driving', 'category' => 'physical', 'description' => 'Driving vehicles'],
            ['name' => 'Sports Coaching', 'category' => 'physical', 'description' => 'Coaching sports and fitness'],
            
            // Language Skills
            ['name' => 'Spanish', 'category' => 'language', 'description' => 'Spanish language proficiency'],
            ['name' => 'French', 'category' => 'language', 'description' => 'French language proficiency'],
            ['name' => 'German', 'category' => 'language', 'description' => 'German language proficiency'],
            ['name' => 'Mandarin', 'category' => 'language', 'description' => 'Mandarin Chinese proficiency'],
            ['name' => 'Sign Language', 'category' => 'language', 'description' => 'Sign language proficiency'],
            ['name' => 'Translation', 'category' => 'language', 'description' => 'Translating between languages'],
            
            // Other Skills
            ['name' => 'Fundraising', 'category' => 'other', 'description' => 'Raising funds for organizations'],
            ['name' => 'Grant Writing', 'category' => 'other', 'description' => 'Writing grant proposals'],
            ['name' => 'Legal Knowledge', 'category' => 'other', 'description' => 'Understanding of legal matters'],
            ['name' => 'Medical Knowledge', 'category' => 'other', 'description' => 'Medical and healthcare knowledge'],
            ['name' => 'Environmental Science', 'category' => 'other', 'description' => 'Knowledge of environmental issues'],
            ['name' => 'Animal Care', 'category' => 'other', 'description' => 'Caring for animals'],
            ['name' => 'Child Care', 'category' => 'other', 'description' => 'Caring for children'],
            ['name' => 'Elder Care', 'category' => 'other', 'description' => 'Caring for elderly individuals'],
        ];

        foreach ($skills as $skill) {
            Skill::firstOrCreate(
                ['name' => $skill['name']],
                [
                    'category' => $skill['category'],
                    'description' => $skill['description'],
                    'is_active' => true
                ]
            );
        }
    }
}
