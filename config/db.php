<?php

use craft\config\DbConfig;

return DbConfig::create()
    ->charset('utf8mb4')
    ->collation('utf8mb4_unicode_ci')
;
