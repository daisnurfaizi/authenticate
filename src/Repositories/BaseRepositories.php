<?php

namespace Ijp\Auth\Repositories;

use Illuminate\Database\Eloquent\Model;
use ReflectionClass;
use ReflectionProperty;

abstract class BaseRepositories
{
    protected $model;

    public  function  __construct(Model $model)
    {
        $this->model = $model;
    }

    public  function  getAll()
    {
        return $this->model->all();
    }

    public  function  create($data)
    {
        return $this->model::create($data);
    }


    public  function  delete($id)
    {
        return $this->model->destroy($id);
    }

    public  function  show($id)
    {
        return $this->model->findOrFail($id);
    }

    public function showAllByFilter($columns = array('*'), $like = false, $filter = null)
    {
        // Membuat query dasar menggunakan model
        $query = $this->model;
        if ($like && $filter) {
            $query->where(function ($query) use ($filter) {
                foreach ($filter as $key => $param) {
                    $query->orWhere($key, 'like', '%' . $param . '%');
                }
            });
        }

        return $query->get($columns);
    }

    public  function  showBy($field, $value, $columns = array('*'))
    {
        return $this->model->where($field, $value)->first($columns);
    }

    public function showByAll($field, $value, $columns = array('*'), $like = false, $filter = null)
    {
        if (is_array($field) && is_array($value)) {
            $operator = isset($operators[0]) ? $operators[0] : '=';
            $query = $this->model->where($field[0], $operator, $value[0]);
            for ($i = 1; $i < count($field); $i++) {
                $operator = isset($operators[$i]) ? $operators[$i] : '=';
                $query = $query->where($field[$i], $operator, $value[$i]);
            }
        } else {
            $query = $this->model->where($field, $value);
        }


        if ($like && $filter) {
            $query = $query->where(function ($query) use ($filter) {
                foreach ($filter as $key => $param) {
                    $query->orWhere($key, 'like', '%' . $param . '%');
                }
            });
        }

        return $query->get($columns);
    }
    public function showWithRelation($columns = ['*'], $with = [], $filter = null, $where = [], $orderBy = [])
    {
        // Membuat query dasar menggunakan model
        $query = $this->model;

        // Menentukan kolom yang akan dipilih
        if (!empty($columns) && $columns !== ['*']) {
            $query = $query->select($columns);
        }

        // Menambahkan kondisi 'where' berdasarkan parameter yang diberikan
        if (!empty($where)) {
            foreach ($where as $field => $value) {
                $query = $query->where($field, $value);
            }
        }

        // Menambahkan relasi yang akan dimuat
        if (!empty($with)) {
            $query = $query->with($with);
        }

        // Menambahkan kondisi 'filter' dengan pencarian like
        if (!empty($filter)) {
            $query = $query->where(function ($query) use ($filter) {
                foreach ($filter as $key => $param) {
                    $query->orWhere($key, 'like', '%' . $param . '%');
                }
            });
        }

        // Menambahkan kondisi 'orderBy' berdasarkan parameter yang diberikan
        if (!empty($orderBy)) {
            foreach ($orderBy as $field => $direction) {
                $query = $query->orderBy($field, $direction);
            }
        }

        // Mengambil data dengan hasil akhir query
        return $query->get();
    }



    public function showByFieldParam(
        $field,
        $columns = array('*'),
        $like = false,
        $filter = null,
        $between = null,
        $excel = false,
        $relations = [],
        $relationSearch = []

    ) {
        // dd($columns);
        $query = $this->model;
        // Jika ada relasi yang akan di-load
        if (!empty($relations)) {
            $query = $query->with($relations);
        }

        // Melakukan pencarian dinamis berdasarkan relasi
        // dd($relationSearch);
        if (!empty($relationSearch)) {
            foreach ($relationSearch as $relationPath => $criteria) {
                $query = $query->whereHas($relationPath, function ($q) use ($criteria) {
                    foreach ($criteria as $column => $values) {
                        if (is_array($values)) {
                            $q->whereIn($column, $values);
                        } else {
                            $q->where($column, $values);
                        }
                    }
                });
            }
        }


        if (is_array($field)) {
            foreach ($field as $key => $value) {
                // Jika $key adalah array, maka gunakan whereIn dan explode $value
                if (is_array($value)) {
                    $query = $query->whereIn($key, $value);
                } elseif ($key === 'age') {
                    $query = $query->whereRaw("TIMESTAMPDIFF(YEAR, tgl_lhr, CURDATE()) = ?", [$value]);
                } else {
                    $query = $query->where($key, $value);
                }
            }
        }

        // return $query;

        if ($like && $filter) {
            $query = $query->where(function ($query) use ($filter) {
                foreach ($filter as $key => $param) {
                    $query->orWhere($key, 'like', '%' . $param . '%');
                }
            });
        }

        if ($between) {
            // Pastikan $between adalah array dengan 3 elemen
            if (count($between) == 3) {
                $query = $query->whereBetween($between[0], [$between[1], $between[2]]);
            } else {
                // Handle error
                throw new \Exception('Invalid between parameter. Expected array with 3 elements.');
            }
        }

        if ($excel) {
            return $query;
            // return true;
        } else {
            // dd($query);
            // dd($query->get($columns));
            return $query->get($columns);
        }
    }
    public  function  getModels()
    {
        return $this->model;
    }

    public function  softDelete($id)
    {
        $record = $this->model::where('id', $id)->delete();
        return $record;
    }

    public function nonActive($id)
    {
        $record = $this->model::where('id', $id)->update(['flag_aktif' => 0]);
        return $record;
    }

    public function active($id)
    {
        $record = $this->model::where('id', $id)->update(['flag_aktif' => 1]);
        return $record;
    }

    public function updateDetailBy($entity, $method = 'getNik', $field = 'nik')
    {
        // dd($entity);
        if (!method_exists($entity, $method)) {
            throw new \Exception("Entity tidak memiliki metode " . $method);
        }

        $value = $entity->$method();
        $profil = $this->model->where($field, $value)->first();
        // dd($profil);

        if (!$profil) {
            throw new \Exception("Data tidak ditemukan");
        }

        // Membuat instance baru dari kelas ReflectionClass
        $reflection = new ReflectionClass($entity);

        // Meminta semua properti dari objek $entity yang memiliki visibilitas private
        $properties = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);

        // Membuat array kosong untuk menyimpan nilai-nilai dari properti objek
        $values = [];
        // Melewati setiap properti dari objek $entity
        foreach ($properties as $property) {
            // Mengubah setiap properti menjadi dapat diakses sehingga kita dapat membaca nilai dari properti tersebut
            $property->setAccessible(true);
            // Mendapatkan nilai dari setiap properti
            $propertyValue = $property->getValue($entity);
            // Memeriksa apakah nilai dari properti telah diatur dan bukan string kosong
            if (isset($propertyValue) && $propertyValue !== "") {
                // Menambahkan nilai dari properti tersebut ke dalam array $values
                // Key dari setiap item adalah nama dari properti tersebut
                $values[$property->getName()] = $propertyValue;
            }
        }
        // dd($values);
        $profil->update($values);
    }

    public function createByEntity($entity)
    {
        $reflection = new ReflectionClass($entity);

        $properties = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);

        $values = [];

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $propertyValue = $property->getValue($entity);
            if (isset($propertyValue) && $propertyValue !== "") {
                $values[$property->getName()] = $propertyValue;
            }
        }
        $insert = $this->model->create($values);
        return $insert;
    }
}
