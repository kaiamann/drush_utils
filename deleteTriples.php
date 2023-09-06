<?php

use \Drupal\wisski_adapter_sparql11_pb\Plugin\wisski_salz\Engine\Sparql11EngineWithPB;

/**
 * DELETES EVERY TRIPLE IN THE TRIPLESTORE
 */
function clearTriplestore(){
  $adapters = \Drupal::entityTypeManager()->getStorage('wisski_salz_adapter')->loadMultiple();

  foreach($adapters as $name =>$adapter){
    $engine = $adapter->getEngine();
    // skip if not a SPARQL engine
    if(!$engine instanceof Sparql11EngineWithPB){
      continue;
    }
    // Do the deleting
    $query = "CLEAR ALL";
    $engine->directUpdate($query);
  }
  \Drupal::messenger()->addMessage("Successfully deleted all triples.");
}

clearTriplestore();