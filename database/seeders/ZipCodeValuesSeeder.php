<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ZipCodeValuesSeeder extends Seeder
{
    // Example: muncity_id => zip_code
    public function run()
    {

        $zipCodes = [
            // Bohol
            1 => '6302', // Alburquerque
            2 => '6314', // Alicia
            3 => '6311', // Anda
            4 => '6335', // Antequera
            5  => '6301', // Baclayon 
            6 => '6342', // Balilihan 
            7 => '6318', // Batuan
            8 => '6326', // Bien Unido
            9 => '6317', // Bilar
            10 => '6333', // Buenavista
            11 => '6328', // Calape
            12 => '6312', // Candijay
            13 => '6319', // Carmen
            14 => '6343', // Catigbian
            15 => '6330', // Clarin
            16 => '6337', // Corella
            17 => '6341', // Cortes
            18 => '6322', // Dagohoy
            19 => '6344', // Danao
            20 => '6339', // Dauis
            21 => '6305', // Dimiao
            22 => '6309', // Duero
            23 => '6307', // Garcia Hernandez
            24 => '6310', // Guindulman
            25 => '6332', // Inabanga
            26 => '6308', // Jagna
            27 => '6334', // Jetafe
            28 => '6304', // Lila
            29 => '6303', // Loay
            30 => '6316', // Loboc
            31 => '6327', // Loon
            32 => '6313', // Mabini
            33 => '6336', // Maribojoc
            34 => '6340', // Panglao
            35 => '6321', // Pilar
            36 => '6346', // President Carlos P. Garcia (Pitogo)
            37 => '6331', // Sagbayan (Borja)
            38 => '6345', // San Isidro
            39 => '6323', // San Miguel
            40 => '6347', // Sevilla
            41 => '6320', // Sierra Bullones
            42 => '6338', // Sikatuna
            43 => '6300', // Tagbilaran City (Capital)
            44 => '6325', // Talibon
            45 => '6324', // Trinidad
            46 => '6329', // Tubigon
            47 => '6315', // Ubay
            48 => '6306', // Valencia

            // Cebu 
            49 => '6033', // Alcantara
            50 => '6023', // Alcoy
            51 => '6030', // Alegria
            52 => '6040', // Aloguinsan
            53 => '6021', // Argao
            54 => '6042', // Asturias
            55 => '6031', // Badian
            56 => '6041', // Balamban
            57 => '6052', // Bantayan
            58 => '6036', // Barili 
            59 => '6024', // Boljoon
            60 => '6008', // Borbon
            61 => '6005', // Carmen
            62 => '6006', // Catmon
            63 => '6000', // Cebu City (Capital)
            64 => '6010', // City Of Bogo
            65 => '6019', // City Of Carcar
            66 => '6037', // City Of Naga
            67 => '6045', // City Of Talisay
            68 => '6003', // Compostela
            69 => '6001', // Consolacion
            70 => '6017', // Cordova
            71 => '6013', // Daanbantayan
            72 => '6022', // Dalaguete
            73 => '6004', // Danao City
            74 => '6035', // Dumanjug
            75 => '6026', // Ginatilan
            76 => '6015', // Lapu - lapu City(opon)
            77 => '6002', // Liloan
            78 => '6053', // Madridejos
            79 => '6029', // Malabuyoc
            80 => '6014', // Mandaue City
            81 => '6012', // Medellin
            82 => '6046', // Minglanilla
            83 => '6032', // Moalboal
            84 => '6025', // Oslob
            85 => '6048', // Pilar
            86 => '6039', // Pinamungahan
            87 => '6049', // Poro
            88 => '6034', // Ronda
            89 => '6027', // Samboan
            90 => '6018', // San Fernando
            91 => '6050', // San Francisco
            92 => '6011', // San Remigio
            93 => '6047', // Santa Fe
            94 => '6026', // Santander
            95 => '6020', // Sibonga
            96 => '6007', // Sogod
            97 => '6009', // Tabogon
            98 => '6044', // Tabuelan
            99 => '6038', // Toledo City
            100 => '6043', // Tuburan
            101 => '6051', // Tudela

            // Add mo re muncity_id => zip_code pairs as needed
        ];

        foreach ($zipCodes as $muncityId => $zipCode) {
            DB::table('muncity')
                ->where('id', $muncityId)
                ->update(['zip_code' => (int)$zipCode]);
        }
    }
}
