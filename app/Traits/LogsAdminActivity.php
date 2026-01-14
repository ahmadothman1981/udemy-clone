<?php

namespace App\Traits;

use App\Models\AdminAuditLog;

trait LogsAdminActivity
{
    /**
     * Log an admin action
     */
    protected function logAction(
        string $action,
        $target = null,
        array $oldValues = null,
        array $newValues = null,
        string $notes = null
    ): AdminAuditLog {
        return AdminAuditLog::log($action, $target, $oldValues, $newValues, $notes);
    }

    /**
     * Log user-related action
     */
    protected function logUserAction(string $action, $user, array $changes = null, string $notes = null): AdminAuditLog
    {
        return $this->logAction("user.{$action}", $user, null, $changes, $notes);
    }

    /**
     * Log course-related action
     */
    protected function logCourseAction(string $action, $course, array $changes = null, string $notes = null): AdminAuditLog
    {
        return $this->logAction("course.{$action}", $course, null, $changes, $notes);
    }

    /**
     * Log instructor-related action
     */
    protected function logInstructorAction(string $action, $instructor, array $changes = null, string $notes = null): AdminAuditLog
    {
        return $this->logAction("instructor.{$action}", $instructor, null, $changes, $notes);
    }

    /**
     * Log enrollment-related action
     */
    protected function logEnrollmentAction(string $action, $enrollment, string $notes = null): AdminAuditLog
    {
        return $this->logAction("enrollment.{$action}", $enrollment, null, null, $notes);
    }

    /**
     * Log moderation action (review, question, answer)
     */
    protected function logModerationAction(string $type, string $action, $target, string $notes = null): AdminAuditLog
    {
        return $this->logAction("{$type}.{$action}", $target, null, null, $notes);
    }

    /**
     * Log settings change
     */
    protected function logSettingsAction(string $action, array $oldValues = null, array $newValues = null, string $notes = null): AdminAuditLog
    {
        return $this->logAction("setting.{$action}", null, $oldValues, $newValues, $notes);
    }

    /**
     * Log category action
     */
    protected function logCategoryAction(string $action, $category, string $notes = null): AdminAuditLog
    {
        return $this->logAction("category.{$action}", $category, null, null, $notes);
    }
}
