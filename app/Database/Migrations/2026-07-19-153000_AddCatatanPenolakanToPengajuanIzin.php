<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCatatanPenolakanToPengajuanIzin extends Migration
{
    public function up(): void
    {
        if (!$this->db->fieldExists('catatan_penolakan', 'pengajuan_izin')) {
            $this->forge->addColumn('pengajuan_izin', [
                'catatan_penolakan' => [
                    'type'    => 'TEXT',
                    'null'    => true,
                    'default' => null,
                    'after'   => 'status',
                ],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->fieldExists('catatan_penolakan', 'pengajuan_izin')) {
            $this->forge->dropColumn('pengajuan_izin', 'catatan_penolakan');
        }
    }
}
