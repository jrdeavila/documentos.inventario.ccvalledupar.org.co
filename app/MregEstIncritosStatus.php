<?php

namespace App;


enum MregEstIncritosStatus: string
{
    case MI = "MI (Matricula Inactiva)";
    case IA = "IA (Inscripción Activa)";
    case IC = "IC (Inscripción Cancelada)";
    case IF = "IF (Inscripción Fuera de Jurisdicción)";
    case II = "II (Inscripción Inactiva)";
    case IS = "IS (Inscripción sin Competencias)";
    case MA = "MA (Matricula Activa)";
    case MC = "MC (Matricula Cancelada)";
    case MF = "MF (Matricula Fuera de Jurisdicción)";
    case NA = "NA (No Asignada)";
    case NM = "NM (No Matriculada)";
    case AS = "AS (Desconocido)";
    case MG = "MG (Desconocido)";
}
