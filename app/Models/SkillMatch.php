<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SkillMatch extends Model
{
    protected $fillable = [
        'user_id',
        'opportunity_id',
        'match_score',
        'matched_skills',
        'missing_skills',
        'is_notified',
        'calculated_at'
    ];

    protected $casts = [
        'matched_skills' => 'array',
        'missing_skills' => 'array',
        'is_notified' => 'boolean',
        'calculated_at' => 'datetime',
        'match_score' => 'decimal:2'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function opportunity()
    {
        return $this->belongsTo(Opportunity::class);
    }

    // Scopes
    public function scopeHighMatch($query, $threshold = 70)
    {
        return $query->where('match_score', '>=', $threshold);
    }

    public function scopeUnnotified($query)
    {
        return $query->where('is_notified', false);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('calculated_at', '>=', Carbon::now()->subDays($days));
    }

    // Helper methods
    public function getMatchQualityAttribute()
    {
        if ($this->match_score >= 90) return 'excellent';
        if ($this->match_score >= 75) return 'good';
        if ($this->match_score >= 60) return 'fair';
        return 'poor';
    }

    public function getMatchQualityColorAttribute()
    {
        switch ($this->match_quality) {
            case 'excellent': return 'green';
            case 'good': return 'blue';
            case 'fair': return 'yellow';
            default: return 'gray';
        }
    }

    public function markAsNotified()
    {
        $this->update(['is_notified' => true]);
    }

    // Static methods for skill matching
    public static function calculateMatch(User $user, Opportunity $opportunity)
    {
        $userSkills = $user->skills()->get();
        $opportunitySkills = $opportunity->skills()->get();

        if ($opportunitySkills->isEmpty()) {
            return null; // No skills required for this opportunity
        }

        $matchedSkills = [];
        $missingSkills = [];
        $totalScore = 0;
        $maxPossibleScore = 0;

        foreach ($opportunitySkills as $oppSkill) {
            $maxPossibleScore += $oppSkill->pivot->is_required ? 20 : 10;
            
            $userSkill = $userSkills->firstWhere('id', $oppSkill->id);
            
            if ($userSkill) {
                $userLevel = $userSkill->pivot->proficiency_level;
                $requiredLevel = $oppSkill->pivot->required_level;
                
                $levelScore = self::calculateLevelScore($userLevel, $requiredLevel);
                $skillScore = ($oppSkill->pivot->is_required ? 20 : 10) * ($levelScore / 100);
                
                $totalScore += $skillScore;
                
                $matchedSkills[] = [
                    'skill_id' => $oppSkill->id,
                    'skill_name' => $oppSkill->name,
                    'user_level' => $userLevel,
                    'required_level' => $requiredLevel,
                    'is_required' => $oppSkill->pivot->is_required,
                    'score' => $skillScore
                ];
            } else {
                $missingSkills[] = [
                    'skill_id' => $oppSkill->id,
                    'skill_name' => $oppSkill->name,
                    'required_level' => $oppSkill->pivot->required_level,
                    'is_required' => $oppSkill->pivot->is_required
                ];
            }
        }

        $matchScore = $maxPossibleScore > 0 ? ($totalScore / $maxPossibleScore) * 100 : 0;

        return [
            'match_score' => round($matchScore, 2),
            'matched_skills' => $matchedSkills,
            'missing_skills' => $missingSkills
        ];
    }

    private static function calculateLevelScore($userLevel, $requiredLevel)
    {
        $levels = ['beginner' => 1, 'intermediate' => 2, 'advanced' => 3, 'expert' => 4];
        
        $userLevelNum = $levels[$userLevel] ?? 2;
        $requiredLevelNum = $levels[$requiredLevel] ?? 2;
        
        if ($userLevelNum >= $requiredLevelNum) {
            return 100; // Perfect match or overqualified
        } else {
            // Partial credit for being close
            return max(0, 100 - (($requiredLevelNum - $userLevelNum) * 25));
        }
    }

    public static function updateMatchesForUser(User $user)
    {
        $opportunities = Opportunity::whereHas('skills')->get();
        
        foreach ($opportunities as $opportunity) {
            $matchData = self::calculateMatch($user, $opportunity);
            
            if ($matchData) {
                self::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'opportunity_id' => $opportunity->id
                    ],
                    [
                        'match_score' => $matchData['match_score'],
                        'matched_skills' => $matchData['matched_skills'],
                        'missing_skills' => $matchData['missing_skills'],
                        'calculated_at' => now(),
                        'is_notified' => false
                    ]
                );
            }
        }
    }

    public static function updateMatchesForOpportunity(Opportunity $opportunity)
    {
        $users = User::whereHas('skills')->get();
        
        foreach ($users as $user) {
            $matchData = self::calculateMatch($user, $opportunity);
            
            if ($matchData) {
                self::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'opportunity_id' => $opportunity->id
                    ],
                    [
                        'match_score' => $matchData['match_score'],
                        'matched_skills' => $matchData['matched_skills'],
                        'missing_skills' => $matchData['missing_skills'],
                        'calculated_at' => now(),
                        'is_notified' => false
                    ]
                );
            }
        }
    }
}
