-- MindMerge SmartCampus exam/results migration
-- Adds class-section scheduling fields to exams and links results to exams.

ALTER TABLE `exams`
  ADD COLUMN IF NOT EXISTS `exam_code` varchar(40) DEFAULT NULL AFTER `exam_id`,
  ADD COLUMN IF NOT EXISTS `class_id` int(11) DEFAULT NULL AFTER `exam_type_id`,
  ADD COLUMN IF NOT EXISTS `section_id` int(11) DEFAULT NULL AFTER `class_id`,
  ADD COLUMN IF NOT EXISTS `exam_date` date DEFAULT NULL AFTER `academic_year`,
  ADD COLUMN IF NOT EXISTS `exam_time` time DEFAULT NULL AFTER `exam_date`;

UPDATE `exams`
SET `exam_code` = CONCAT('EXM-', DATE_FORMAT(COALESCE(`created_at`, NOW()), '%Y%m%d'), '-', `exam_id`)
WHERE `exam_code` IS NULL OR `exam_code` = '';

UPDATE `exams`
SET `status` = CASE WHEN `status` = 'archived' THEN 'inactive' ELSE 'active' END;

ALTER TABLE `exams`
  MODIFY `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  ADD UNIQUE KEY IF NOT EXISTS `unique_exam_code` (`exam_code`),
  ADD KEY IF NOT EXISTS `idx_exams_class_section` (`class_id`, `section_id`),
  ADD KEY IF NOT EXISTS `idx_exams_date` (`exam_date`);

ALTER TABLE `results`
  ADD COLUMN IF NOT EXISTS `exam_id` int(11) DEFAULT NULL AFTER `result_id`,
  ADD UNIQUE KEY IF NOT EXISTS `unique_result_exam` (`exam_id`);
