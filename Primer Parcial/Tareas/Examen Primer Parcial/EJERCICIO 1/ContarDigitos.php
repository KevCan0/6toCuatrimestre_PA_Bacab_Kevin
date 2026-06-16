<?php
    function contarDigitos($numero){
        $numero = abs(intval($numero));

        if ($numero <10){
            return 1;
        }
        else{
        $numeroReducido = intval ($numero/10);
        return 1 + contarDigitos($numeroReducido);
        }
    }
    echo contarDigitos(38342);
?>