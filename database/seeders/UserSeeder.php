<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'PINKY TUMALIUAN JIMENEZ', 'email' => 'pinky.jimenez@dict.gov.ph', 'role' => 'admin'],
            ['name' => 'MARK JOHN CUDIAMAT TUMALIUAN', 'email' => 'markjohn.tumaliuan@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'SAMANTHA PAYUKET DAWIDEO', 'email' => 'samantha.dawideo@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'MINA FLOR TALAY VILLAFUERTE', 'email' => 'mina.villafuerte@dict.gov.ph', 'role' => 'admin'],
            ['name' => 'JEMAR JAY CALAYAN DEL ROSARIO', 'email' => 'jemar.delrosario@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'LOT-LOT ACERA ABRIGO', 'email' => 'lotlot.acera@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'JAYFER TABAO-ICAN AMMASI', 'email' => 'jayfer.ammasi@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'CHRISTINE JOYCE VILLALUZ BUENO', 'email' => 'christine.bueno@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'EDWARD CABUTAJE MANUEL', 'email' => 'edward.manuel@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'EDMUND CABUTAJE MANUEL', 'email' => 'edmund.manuel@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'ELON FLORES DOMINGO', 'email' => 'elon.domingo@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'CLARO MATAMMU MAGGAY', 'email' => 'claro.maggay@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'PABLO GERALDO FUGABAN', 'email' => 'pablo.fugaban@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'NODEL MEMAN TUMALIUAN', 'email' => 'nodel.tumaliuan@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'KIMBERLY ANN BANATAO CALAYAN', 'email' => 'kimberly.calayan@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'JULIUS CEZAR CALIGUIRAN BAQUIRAN', 'email' => 'juliuscezar.baquiran@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'MAR ELVISON ANGOBONG BAQUIRAN', 'email' => 'mar.baquiran@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'JENNY SALA PRUDENCIADO', 'email' => 'jenny.prudenciado@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'EDWARD MARK DELOS SANTOS ARGONZA', 'email' => 'edwardmark.argonza@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'SHEREEN JUNGCO TABUG', 'email' => 'shereen.tabug@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'LEONOR JUANDAY TUMALIUAN', 'email' => 'leonor@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'MARIZA NOVA MIRANDA MONTES', 'email' => 'mariza.montes@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'IVAN PAUL MATIAS SANTOS', 'email' => 'ivannpaul.santos@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'MAGDALENA DACUYCUY GOMEZ', 'email' => 'magie.gomez@dict.gov.ph', 'role' => 'admin'],
            ['name' => 'JOHNY CALAYAN BATANG', 'email' => 'johny.batang@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'JOEY MARK DE GUZMAN ELCHICO', 'email' => 'joeymark.elchico@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'JANET TUNQUE CATINOY', 'email' => 'janet.catinoy@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'CYRILL SHANE TAGUIAM CEPEDA', 'email' => 'cyrill.cepeda@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'DAN MARK RILLERA JOSE', 'email' => 'danmark.jose@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'MICHAEL ANGELO ROBLES LANGCAY', 'email' => 'michaelangelo.langcay@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'RICA VALENTINO CASUGA', 'email' => 'rica.casuga@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'NEIL KRISTOPHER CONEL GUIMMAYEN', 'email' => 'neilkristopher.guimmayen@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'ROEL URSUA JIMENEZ', 'email' => 'roel.jimenez@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'KYLE RUZZEL CARONAN SUYU', 'email' => 'kyle.suyu@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'RICO GUMARANG BANAN', 'email' => 'rico@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'SAMANTHA PAYUKET DAWIDEO', 'email' => 'samantha.payuket@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'CZYIONE DAYL ASUNCION MENDOZA', 'email' => 'cyzione.mendoza@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'CHRISTIAN LAZARO FLORES CALDEZ', 'email' => 'christian@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'JEI ARISTON CASTILLO JIMENEZ', 'email' => 'jeiariston.jimenez@dict.gov.ph ', 'role' => 'employee'],
            ['name' => 'VLADIMIR VIKTOR GACUTAN NUVAL', 'email' => 'vladimir.nuval@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'JOYCE ANN PADER URDILLAS', 'email' => 'joyce@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'NARDO ABARRA LIM', 'email' => 'nardo.lim@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'MARIO JR ANTONIO DECAPIA', 'email' => 'mario.decapia@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'GLENARD FERNANDO MARTIN', 'email' => 'glenard@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'ARIES ANTHONY FUGGAYGUIM', 'email' => 'aries.guim@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'CHRISTIAN DALE COSTALES AGUDA', 'email' => 'christian.aguda@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'RONALD SIUAGAN BARIUAN', 'email' => 'ronie.bariuan@dict.gov.ph', 'role' => 'admin'],
            ['name' => 'ROLAND BARTILAD HUBALDE', 'email' => 'roland.hubalde@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'ADELMO GATO CANO', 'email' => 'adelmo.gato@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'LEAH GALAROSSA GALOLO', 'email' => 'leah.galolo@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'ALISON AHMAD LLAUDERES ABBAS', 'email' => 'alison.abbas@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'HERLYN KAYE CALDITO NATIVIDAD', 'email' => 'herlyn@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'ROGELIO TAGUIBAO LAYUGAN', 'email' => 'rogelio.layugan@dict.gov.ph', 'role' => 'admin'],
            ['name' => 'FERDINAND BARIUAN ABAD', 'email' => 'ferdie.abad@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'RICHARD PINEDA BALIGOD', 'email' => 'ricky.baligod@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'ALVIN BALIGOD BERMEJO', 'email' => 'alvin.bermejo@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'MARICAR SORIANO PECSON', 'email' => 'maricar.pecson@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'JAYMAR CORSINO RECOLIZADO', 'email' => 'jaymar.recolizado@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'DEEJAY GATAN ANAPI', 'email' => 'deejay.anapi@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'YANCEE KEARVIN KYLE AQUINO RAFER', 'email' => 'kyle.rafer@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'CHRISTOPHER ELEESON LARGADO CAPILI', 'email' => 'christopher.capili@dictgov.ph', 'role' => 'employee'],
            ['name' => 'DARLENE JOY BASSIG SEGURITAN', 'email' => 'darlene@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'MAYLANIE ORDOÑO MAGGAY', 'email' => 'maylanie.maggay@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'CIRILO JR. NACINO GAZZINGAN', 'email' => 'jr.gazzingan@dict.gov.ph', 'role' => 'admin'],
            ['name' => 'EUGENE LOMPERO PABRO', 'email' => 'eugene.pabro@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'CONCEPCION CABALONGA NAZARITA', 'email' => 'conie.nazarita@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'DANIEL PADILLA RAMIREZ', 'email' => 'daniel.ramirez@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'MARILYN FLORES ROBLES', 'email' => 'marilyn.robles@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'BRYAN HIDALGO TOMAS', 'email' => 'bryan.tomas@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'LEO JAY LORENZO ALILAM', 'email' => 'leo.alilam@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'LANIE AGTARAP CABACUNGAN', 'email' => 'Lanie.cabacungan@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'DIETHER ALBANO ABAD', 'email' => 'diether.abad@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'MARIA KRISTINE TAGUINOD VALDEZ', 'email' => 'kristine.valdez@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'MARC IVAN DIZON GUILLERMO', 'email' => 'marc@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'EDISON SALVADOR AGAOID', 'email' => 'edison.agaoid@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'MEDY JOSE MOSADAS CABACUNGAN', 'email' => 'jose.cabacungan@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'JEANNE SHANNON ORIAL GARCIA', 'email' => 'jeanne.garcia@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'EXEN BANTIYAN CLARO', 'email' => 'exen@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'JOSEPH ROSAL', 'email' => 'joseph@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'JOHANNA FERIDO TULAUAN', 'email' => 'johanna.tulauan@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'KARL STEVEN ADALEM MADDELA', 'email' => 'karlsteven.maddela@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'DEBORA PIDO BACKIAWAN', 'email' => 'debora.backiawan@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'MOHAMADNOR GURO USMAN', 'email' => 'mohamadnor.usman@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'MA. ELIJAH HUGO PILOTIN', 'email' => 'elijah@dict.gov.ph', 'role' => 'employee'],
            ['name' => 'HUMAN RESOURCE REGION II', 'email' => 'hr.region2@dict.gov.ph', 'role' => 'admin'],
        ];

        $timestamp = Carbon::now();

        // Prefer SEED_USER_PASSWORD from env for local/dev. If unset, each user
        // gets a unique random password (accounts are unusable until reset).
        $shared = env('SEED_USER_PASSWORD');

        foreach ($users as &$user) {
            $plain = $shared ?: Str::password(24);
            $user['password'] = Hash::make($plain);
            $user['email_verified_at'] = null;
            $user['remember_token'] = null;
            $user['created_at'] = $timestamp;
            $user['updated_at'] = $timestamp;
        }
        unset($user);

        DB::table('users')->insert($users);
    }
}
