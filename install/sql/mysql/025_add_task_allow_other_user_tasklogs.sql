-- This will add a field to the tasks table indicating if users with the
-- proper permissions can add tasks for other users
ALTER TABLE `tasks` ADD COLUMN `task_allow_other_user_tasklogs` int(1) NOT NULL DEFAULT '0';

