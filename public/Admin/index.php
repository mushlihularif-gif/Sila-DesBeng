<?php
// Simple redirect to the Dashboard route.
// This forces the request out of the "directory" context and into the Laravel Route context.
header('Location: /admin/dashboard');
exit;
