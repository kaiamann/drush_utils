<?php

use \Drupal\wisski_adapter_sparql11_pb\Plugin\wisski_salz\Engine\Sparql11EngineWithPB;

deleteBundles();
clearDatabase();
clearTriplestore();
drupal_flush_all_caches();

/**
 * Deletes all WissKI bundles
 */
function deleteBundles(){
  $bundles = \Drupal::entityTypeManager()->getStorage('wisski_bundle')->loadMultiple();
  foreach($bundles as $bundle){
    $bundle->delete();
  }
  \Drupal::messenger()->addMessage("Successfully deleted all bundles.");
}

/**
 * Hopefully clears the database from any bundle artifacts.
 *
 * Truncates following tables:
 * - wisski_salz_id2uri
 * - wisski_calling_bundles
 * - wisski_title_n_grams
 */
function clearDatabase(){
  // clears EID<->URI mappings
  // clearing this might not even be necessary
  \Drupal::database()->delete('wisski_salz_id2uri')->execute();
  // clears EID<->BundleID mappings
  \Drupal::database()->delete('wisski_calling_bundles')->execute();
  // clears titles for entities and the contained BundleID<->EID mappings
  \Drupal::database()->delete('wisski_title_n_grams')->execute();
  \Drupal::messenger()->addMessage("Successfully cleared the database.");
}

/**
 * Clears all URI<->EID mappings from the triplestore
 *
 * Deletes following Graphs:
 * - baseFields
 * - originatesFrom
 */
function clearTriplestore(){
  $adapters = \Drupal::entityTypeManager()->getStorage('wisski_salz_adapter')->loadMultiple();

  foreach($adapters as $name =>$adapter){
    $engine = $adapter->getEngine();
    // skip if not a SPARQL engine
    if(!$engine instanceof Sparql11EngineWithPB){
      continue;
    }

    // get current baseUri from adapter
    $baseUri = $engine->getConfiguration()['default_graph'];
    if(!$baseUri){
      continue;
    }
    // build base fields and originatesFrom graph URIs
    $originatesFrom = "<{$baseUri}originatesFrom>";
    $baseFields = "<{$baseUri}baseFields>";

    // do the deleting
    $query = "DELETE WHERE { GRAPH $originatesFrom { ?s ?p ?o } };";
    $query .= "DELETE WHERE { GRAPH $baseFields { ?s ?p ?o } }";
    $engine->directUpdate($query);
  }
  \Drupal::messenger()->addMessage("Successfully cleared the triplestore.");
}

