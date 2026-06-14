-- MindMerge SmartCampus — Notifications schema improvements
-- Run against mindmerge database (MariaDB 10.4+ / MySQL 8+)
-- Safe to run multiple times: check columns/indexes before applying in production.

-- ---------------------------------------------------------------------------
-- 1. Extensibility columns for automatic notifications from other modules
-- ---------------------------------------------------------------------------

ALTER TABLE `notifications`
  ADD COLUMN IF NOT EXISTS `source_module` VARCHAR(50) NULL DEFAULT NULL AFTER `type`,
  ADD COLUMN IF NOT EXISTS `source_ref` VARCHAR(100) NULL DEFAULT NULL AFTER `source_module`;

-- MariaDB 10.4 may not support IF NOT EXISTS on ADD COLUMN; use manual check if needed:
-- ALTER TABLE notifications ADD COLUMN source_module VARCHAR(50) NULL AFTER type;
-- ALTER TABLE notifications ADD COLUMN source_ref VARCHAR(100) NULL AFTER source_module;

-- ---------------------------------------------------------------------------
-- 2. Indexes for notification_targets (currently missing)
-- ---------------------------------------------------------------------------

ALTER TABLE `notification_targets`
  ADD INDEX IF NOT EXISTS `idx_notification_id` (`notification_id`),
  ADD INDEX IF NOT EXISTS `idx_target_lookup` (`target_type`, `target_value`);

-- ---------------------------------------------------------------------------
-- 3. Indexes for notification_reads
-- ---------------------------------------------------------------------------

ALTER TABLE `notification_reads`
  ADD INDEX IF NOT EXISTS `idx_user_id` (`user_id`),
  ADD INDEX IF NOT EXISTS `idx_notification_id` (`notification_id`);

-- ---------------------------------------------------------------------------
-- 4. Composite index for listing/filtering notifications
-- ---------------------------------------------------------------------------

ALTER TABLE `notifications`
  ADD INDEX IF NOT EXISTS `idx_type_created` (`type`, `created_at`);

-- ---------------------------------------------------------------------------
-- 5. Foreign keys (recommended for referential integrity)
-- ---------------------------------------------------------------------------

-- Uncomment after verifying no orphan rows exist:

-- ALTER TABLE `notification_targets`
--   ADD CONSTRAINT `fk_nt_notification`
--   FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE;

-- ALTER TABLE `notification_reads`
--   ADD CONSTRAINT `fk_nr_notification`
--   FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE,
--   ADD CONSTRAINT `fk_nr_user`
--   FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- ALTER TABLE `notifications`
--   ADD CONSTRAINT `fk_notifications_created_by`
--   FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- ---------------------------------------------------------------------------
-- 6. Seed quick templates (optional — UI uses built-in templates; DB for admin CRUD later)
-- ---------------------------------------------------------------------------

INSERT INTO `notification_templates` (`template_name`, `type`, `title`, `message`)
SELECT * FROM (
  SELECT 'Attendance Warning', 'attendance', 'Attendance Warning',
         'Your attendance has fallen below the required threshold. Please meet your class advisor promptly.'
  UNION ALL
  SELECT 'Fee Reminder', 'fee', 'Fee Payment Reminder',
         'Your fee payment is pending. Please complete payment before the due date.'
  UNION ALL
  SELECT 'Exam Schedule', 'exam', 'Exam Schedule Published',
         'The examination schedule is now available on the portal.'
  UNION ALL
  SELECT 'Results Published', 'result', 'Results Published',
         'Examination results are now available. Log in to view your mark sheet.'
  UNION ALL
  SELECT 'Holiday Notice', 'holiday', 'Holiday Announcement',
         'The campus will remain closed on the announced holiday.'
  UNION ALL
  SELECT 'Emergency Alert', 'emergency', 'Emergency Alert',
         'Important: Please follow official instructions and stay safe.'
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM notification_templates LIMIT 1);

-- ---------------------------------------------------------------------------
-- Recommendations summary:
-- * notification_targets: idx_notification_id, idx_target_lookup — critical for visibility queries
-- * notification_reads: idx_user_id — critical for unread counts per user
-- * source_module/source_ref — enables attendance, exams, fees modules to trace origin
-- * FK with ON DELETE CASCADE — keeps reads/targets clean when notifications are removed
-- * At scale (100k+ notifications): partition by created_at or archive table for reads
