<?php

namespace App\Models\AnggaranRealisasi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use GroceryCrud\Core\Model;
use GroceryCrud\Core\Model\ModelFieldType;
use Illuminate\Support\Facades\DB;

class CustomRefStatus extends Model
{
    use HasFactory;

    public function getFieldTypes($tableName)
    {
        $fieldTypes = parent::getFieldTypes($tableName);

        $tahunAnggaranFieldType = new ModelFieldType();
        $tahunAnggaranFieldType->dataType = 'varchar';

        $kdsatkerFieldType = new ModelFieldType();
        $kdsatkerFieldType->dataType = 'varchar';

        $kodeRevisiFieldType = new ModelFieldType();
        $kodeRevisiFieldType->dataType = 'varchar';

        $jenisRevisiFieldType = new ModelFieldType();
        $jenisRevisiFieldType->dataType = 'varchar';

        $fieldTypes['tahunanggaran'] = $tahunAnggaranFieldType;
        $fieldTypes['kdsatker'] = $kdsatkerFieldType;
        $fieldTypes['jenis_revisi'] = $jenisRevisiFieldType;
        $fieldTypes['kd_sts_history'] = $kodeRevisiFieldType;


        return $fieldTypes;
    }

    protected function _getQueryModelObject() {
        $customfilter = array('tahunanggaran','kdsatker','jenis_revisi','kd_sts_history');
        $tahunanggaran = session('tahunanggaran');
        // All the custom stuff here
        DB::select('tahunanggaran, idrefstatus, kdsatker, kd_sts_history, jenis_revisi, revisi_ke, pagu_belanja, tgl_revisi, statusimport');
        DB::where([
            ['tahunanggaran','=',$tahunanggaran],
            ['kd_sts_history','LIKE','B%']
        ])->orwhere([
            ['tahunanggaran','=',$tahunanggaran],
            ['kd_sts_history','LIKE','C%'],
            ['flag_update_coa','=',1]
        ]);

        if (!empty($this->_filters)) {
            foreach ($this->_filters as $filter_name => $filter_value) {
                if ($filter_name === 'kd_sts_history') {
                    if (is_array($filter_value)) {
                        foreach ($filter_value as $value) {
                            DB::where('kd_sts_history', $value);
                        }
                    } else {
                        DB::where('kd_sts_history', $filter_value);
                    }

                }
                if ($filter_name === 'tahunanggaran') {
                    if (is_array($filter_value)) {
                        foreach ($filter_value as $value) {
                            DB::like('tahunanggaran', $value);
                        }
                    } else {
                        DB::like('tahunanggaran', $filter_value);
                    }

                }
                if ($filter_name === 'jenis_revisi') {
                    if (is_array($filter_value)) {
                        foreach ($filter_value as $value) {
                            DB::like('jenis_revisi', $value);
                        }
                    } else {
                        DB::like('jenis_revisi', $filter_value);
                    }

                }
                else if ($filter_name === 'kdsatker') {
                    if (is_array($filter_value)) {
                        foreach ($filter_value as $value) {
                            DB::like('kdsatker', $value);
                        }
                    } else {
                        DB::like('kdsatker', $filter_value);
                    }

                }
                else if (!in_array($filter_name, $customfilter)) {
                    if (is_array($filter_value)) {
                        foreach ($filter_value as $value) {
                            DB::like($filter_name, $value);
                        }
                    } else {
                        DB::like($filter_name, $filter_value);
                    }
                }
            }
        }

        if (!empty($this->_filters_or)) {
            foreach ($this->_filters_or as $filter_name => $filter_value) {
                DB::or_like($filter_name, $filter_value);
            }
        }

    }


    public function customlimit(){
        DB::limit($this->limit, ($this->limit * ($this->page - 1)));
    }


    public function getList() {

        $this->_getQueryModelObject();
        $this->customlimit();
        return DB::get($this->tableName)->result_array();
    }

    public function getTotalItems()
    {
        $this->_getQueryModelObject();
        return DB::get($this->tableName)->num_rows();


        // If we don't have any filtering it is faster to have the default total items
        // In case this is more complicated you can add your own code here

    }
}
