<?php die(); ?>
Akeeba Backup 7.8.1.1
================================================================================
+ Namespaced third party dependencies to avoid error caused by third party plugins when setting up a WP-CRON schedule in Akeeba Backup

Akeeba Backup 7.8.1
================================================================================
~ Provision for the OVH CRON jobs in the CLI scripts
# [HIGH] Long running WP-CRON scheduled backups would always use profile #1 when resuming
# [LOW] The backup.php CLI script doesn't warn the user if settings decryption is unavailable.
# [LOW] The Manage Backups page didn't report the WP-CRON backup origin correctly

Akeeba Backup 7.8.0.1
================================================================================
! CORE version caused a Dashboard error by trying to register the widgets only shipped with the Pro version

Akeeba Backup 7.8.0
================================================================================
+ Pseudo-CRON with WP-CRON
+ Admin dashboard widgets
+ Option to treat failed uploads as a backup error
# [LOW] PHP Deprecated notice in the Configuration page (cosmetic)