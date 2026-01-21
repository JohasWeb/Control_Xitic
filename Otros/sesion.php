<?php
session_start();

if (($_SESSION["_Usuario"]) == NULL) {

    /* Se queda adentro */
      echo json_encode(array('success' => 0));

        
}else{
  /* Redirige a la salida */

echo  json_encode(array('success' => 1));

}
?>