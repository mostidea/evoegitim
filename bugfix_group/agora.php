<?php
// Agora uygulama kimlik bilgileri
// Bu dosyayı web root dışına taşımak en güvenlisidir:
//   /var/www/evoegitim/config/agora.php  (public/ dışı)
// Şimdilik public/config/ altında tutuluyorsa nginx'te
// location ~* /config/ { deny all; } kuralı eklenmelidir.

define('AGORA_APP_ID',          '33cbda223ae847f9834ab0aec5290286');
define('AGORA_APP_CERTIFICATE', '07c3a14bcf344b2cbf38131aff0b3dc7');
