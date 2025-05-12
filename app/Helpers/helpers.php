<?php

// Carga todos los archivos PHP del directorio Helpers
foreach (glob(__DIR__ . '/*.php') as $filename) {
    require_once $filename;
}
