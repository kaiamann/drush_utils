<?php

$bundleId = $_SERVER['argv'][3];

$bundle = \Drupal::entityTypeManager()->getStorage('wisski_bundle')->load($bundleId);

return $bundle->label;

