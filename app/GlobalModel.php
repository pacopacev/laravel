<?php

namespace App;

use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Session;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\UploadedFile;

class GlobalModel extends DB {

    private $statuses = ['active', 'inactive', 'proposed', 'revision'];
    protected static $countryCodes = ['0040','00389','0030','00421','00355','00381','00359'];

    public function __construct() {
        if (auth()->id()/* and ! self::$account_id */) {
            DB::statement("SET SESSION nextpro.user_id = '" . auth()->id() . "'");
            DB::statement("SET SESSION nextpro.appl_access = '" . get_class($this) . "'");
            DB::statement("SET SESSION nextpro.rem_addr = '" . \Request::ip() . "'");
        }
    }
    private function list2array($lists) {
        return json_decode(json_encode($lists), true);
    }

    protected function startTrans() {
        DB::beginTransaction();
    }

    protected function commit() {
        DB::commit();
    }

    protected function rollBack() {
        DB::rollBack();
    }

    protected function sqlArray($query, $data = [], $adapter = null) {
        if ($adapter) {
            $db = $this::connection($adapter);
            return $this->list2array($db->select($query, $data));
        }
        return $this->list2array($this::select($query, $data));
    }

    protected function sqlCurrent($query, $data = [], $adapter = null) {
        if ($adapter) {
            $db = $this::connection($adapter);
            $result = $this->list2array($db->select($query, $data));
            return isset($result[0]) ? $result[0] : null;
        }
        $result = $this->list2array($this::select($query, $data));
        return isset($result[0]) ? $result[0] : null;
    }

    protected function updateOrInsert($table, $where, $update, $adapter = null) {
        if ($adapter) {
            $db = $this::connection($adapter);
            return $db->table($table)->updateOrInsert($where, $update);
        }
        return $this::table($table)->updateOrInsert($where, $update);
    }

    protected function massInsert($table, $values, $adapter = null) {
        if ($adapter) {
            $db = $this::connection($adapter);
            return $db->table($table)->insert($values);
        }
        return $this::table($table)->insert($values);
    }

    protected function insertGetId($table, $values, $adapter = null) {
        if ($adapter) {
            $db = $this::connection($adapter);
            return $db->table($table)->insertGetId($values);
        }
        return $this::table($table)->insertGetId($values);
    }

    protected function getLanguage() {
        $lang = 1;
        if (isset(Session::get('language')->id)) {
            $lang = (int) Session::get('language')->id;
        }
        return $lang;
    }

    protected function getAuth() {
        if(auth()->id()){
        return $this->sqlCurrent("select employees.*,accounts.id as account_id,objects.cartel_id,objects.objects_type_id,accounts.username,currencies.id as currency,currencies.rate as nvrate from accounts inner join employees on employees.id=accounts.employee_id left join objects on objects.id=employees.object_id inner join companies on companies.id=employees.company_id left join currencies on currencies.id=companies.currency where accounts.id=" . auth()->id());
        }
    }

    protected function paginate($items, $perPage, $page, $options = []) {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        $itemsTotal = $items->count();
        $paginateItems = new LengthAwarePaginator($items->forPage($page, $perPage), $itemsTotal, $perPage, $page, $options);
        $result = ['success' => true, 'items' => [], 'total' => $itemsTotal];
        foreach ($paginateItems as $key => $value) {
            if (count($value) > 0) {
                $result['items'][] = $value;
            }
        }
        return $result;
    }

    protected function sqlArrayWithPaginator($query, $data = [], $adapter = null, $perPage = 25, $page = null, $options = []) {
        $items = $this->sqlArray($query, $data, $adapter = null);
        return $this->paginate($items, $perPage, $page);
    }

    protected function sqlUpdate($table, $where, $update, $adapter = null) {
        if ($adapter) {
            $db = $this::connection($adapter);
            return $db->table($table)->where($where)->update($update);
        }
        return $this::table($table)->where($where)->update($update);
    }

    protected function sqlSelect($table, $where, $adapter = null) {
        if ($adapter) {
            $db = $this::connection($adapter);
            return $this->list2array($db->table($table)->where($where)->get());
        }
        return $this->list2array($this::table($table)->where($where)->get());
    }

    protected function sqlSelectCurrent($table, $where, $adapter = null) {
        if ($adapter) {
            $db = $this::connection($adapter);
            $return = $this->list2array($db->table($table)->where($where)->get());
            return isset($return[0]) ? $return[0] : null;
        }
        $return = $this->list2array($this::table($table)->where($where)->get());
        return isset($return[0]) ? $return[0] : null;
    }

    protected function sqlDelete($table, $where, $adapter = null) {
        if ($adapter) {
            $db = $this::connection($adapter);
            return $db->table($table)->where($where)->delete();
        }
        return $this::table($table)->where($where)->delete();
    }

    protected function searchStrWords($words, $column) {
        if (count($words) != 2) {
            return '';
        }
        list($word1, $word2) = $words;
        return "(TRIM(REPLACE({$column},E'\n','')) ILIKE '{$word1}%' AND TRIM(REPLACE({$column},E'\n','')) ILIKE '%{$word2}')";
    }

    protected function calcDoubleToTime($double) {
        $whole = (int) $double;
        $decimal = $double - $whole;

        $time = $whole + ((60 * $decimal) / 100);
        $time = round($time, 2);
        $time = sprintf('%.2f', $time);

        list($hours, $minute) = sscanf($time, '%d.%d');

        $minute = sprintf('%02d', (int) $minute);

        return [$hours, $minute];
    }

    protected function fileIsPHP($file, $name) {
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (file_exists($file) and in_array(mime_content_type($file), ['application/x-php', 'text/x-php'])
                or $ext == 'php') {
            return true;
        }

        return false;
    }

    protected function object_to_array($obj) {
        //only process if it's an object or array being passed to the function
        if (is_object($obj) || is_array($obj)) {
            $ret = (array) $obj;
            foreach ($ret as &$item) {
                //recursively process EACH element regardless of type
                $item = $this->object_to_array($item);
            }

            return $ret;
        }

        return $obj;
    }

    public function download($data) {
        $stored = (isset($data['stored']) and $data['stored'] != '') ? $data['stored'] : null;
        $original = (isset($data['original']) and $data['original'] != '') ? $data['original'] : null;
        if (strpos($stored, '../') !== false) {
            exit;
        }
        $stored = __DIR__ . '/../public/files' . $stored;
        if (file_exists($stored)) {
            header('Content-type: ' . mime_content_type($stored));
            header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Pragma: no-cache');
            header('Content-Disposition: attachment; filename="' . $original . '"');
            readfile($stored);
        }
    }

    protected function checkValue($key, $value, $type = 'integer') {
        $arrayData[$key] = $value;
        $validatorArray[$key] = $type;
        \Illuminate\Support\Facades\Validator::extend('numericarray', function($attribute, $value, $parameters) // Проверява масив с инт
        { //  $this->checkValue('suppliers',$suppliers,'required|array|numericarray');
            foreach($value as $v) {if(!is_int($v)) return false;}
            return true;
        });
        if ($type == 'date') {
            if (Carbon::createFromFormat('Y-m-d', $value) == false) {
                return ['false' => false, 'msg' => 'No Date'];
            }
            return $value;
        }
        $validator = \Illuminate\Support\Facades\Validator::make($arrayData, $validatorArray);
        $totalErrors = $validator->fails();
        if ($totalErrors > 0) {
            $errorStr = '';
            $newRow = '';
            foreach ($validator->errors()->all() as $item) {
                if ($totalErrors > 1) {
                    $newRow = '<br/>';
                }
                $errorStr .= $item . $newRow;
            }
            die(json_encode(['success' => false, 'error' => $errorStr]));
        }
        return $value;
    }

    protected function unlinkArray($array) {
        foreach ($array as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    protected function getAllAccessIdEmployees($month = null) {
        $accountId = auth()->id();
        $sql = "select employees.id from accounts_objects left join accounts_objects_jobs ON accounts_objects_jobs.ao_id = accounts_objects.id inner join employees on employees.object_id=accounts_objects.object_id where accounts_objects.account_id = :account_id and accounts_objects_jobs.job_id is null union select employees.id from accounts_objects inner join accounts_objects_jobs on accounts_objects_jobs.ao_id = accounts_objects.id inner join employees on employees.object_id=accounts_objects.object_id and employees.job_id=accounts_objects_jobs.job_id where accounts_objects.account_id = :account_id";
        if ($month) {
            $startData = $month . '-01';
            if (!date_create($startData)) {
                $startData = date('Y-m-01');
            }
            $stopData = date('Y-m-t', strtotime($startData));
            $sql = "select employees.id from accounts_objects inner join employees_schedule on employees_schedule.default_object_id=accounts_objects.object_id and employees_schedule.date between '" . $startData . "' and '" . $stopData . "' inner join employees on employees.id=employees_schedule.employee_id where accounts_objects.account_id = :account_id and accounts_objects.object_id not in ( select accounts_objects.object_id FROM accounts_objects inner join accounts_objects_jobs on accounts_objects_jobs.ao_id = accounts_objects.id where accounts_objects.account_id = :account_id group by accounts_objects.object_id) UNION select employees.id from accounts_objects inner join accounts_objects_jobs on accounts_objects_jobs.ao_id = accounts_objects.id inner join employees_schedule on employees_schedule.default_object_id=accounts_objects.object_id and employees_schedule.job_id = accounts_objects_jobs.job_id and employees_schedule.date between '" . $startData . "' and '" . $stopData . "' inner join employees on employees.id=employees_schedule.employee_id where accounts_objects.account_id = :account_id group by employees.id";
        }
        $items = $this->sqlArray($sql, array(":account_id" => $accountId));
        $result = [];
        foreach ($items as $key => $value) {
            $result[$value['id']] = $value['id'];
        }
        return $result;
    }

    protected function insert($table, $values, $adapter = null) {
        if ($adapter) {
            $db = $this::connection($adapter);
            return $db->table($table)->insert($values);
        }
        return $this::table($table)->insert($values);
    }

    protected function ignorInsert($table, $values, $adapter = null) {
        if ($adapter) {
            $db = $this::connection($adapter);
            return $db->table($table)->insertOrIgnore($values);
        }
        return $this::table($table)->insertOrIgnore($values);
    }

    protected function is_date($date) {
        return !!date_create($date);
    }

    protected function getStatuses() {
        return $this->statuses;
    }

    protected function sort($jSort) { // подава се json от стора и се вкарва в sql заявка
        if (is_array($jSort) && count($jSort) > 0) {
            $orderby = 'ORDER BY ';
            foreach ($jSort as $sort) {
                $property = $this->checkValue('property', $sort['property'], 'required|regex:/(^([a-zA-zа-яА-Я_]+)$)/u|max:50');
                $direction = $this->checkValue('direction', $sort['direction'], 'required|ends_with:ASC,DESC,asc,desc');
                $orderby .= $property . ' ' . $direction . ',';
            }
            return rtrim($orderby, ',');
        }
        return '';
    }

    protected function AccessSpecialUsers() {
        $accessAccIds = [8253, 1, 9202, 8476, 12306, 11512]; // По специална проверка. Слага се в началото на функции.
        if (!in_array(auth()->id(), $accessAccIds)) {
            throw new ValidationException('Нямате правомощия');
            die('Нямате права до този ресурс!!!');
        }
        return true;
    }

    protected function array_orderby() {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row)
                    $tmp[$key] = $row[$field];
                $args[$n] = $tmp;
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return array_pop($args);
        //$sorted = array_orderby($data, 'volume', SORT_DESC, 'edition', SORT_ASC); primer
    }

    protected function update($table, array $values, $whereField, $whereValue) {
        return $this::table($table)->where($whereField, $whereValue)->update($values);
    }

    protected function getTable($table) {
        return $this::table($table);
    }

    protected function generateSqlValueList(array $data, array $casts, int $limit = 0) {
        if (!count($data) or ! count($casts)) {
            throw new \Exception('Function generateSqlValueList get empty array!');
        }
        $dataKeys = array_keys(reset($data));
        $castsKeys = array_keys($casts);
        if (!($dataKeys == $castsKeys)) {
            throw new \Exception('Function generateSqlValueList get different data and cast arrays!');
        }
        $values = [];
        foreach (array_values($data) as $rk => $r) {
            if ($limit == 0 OR $limit >= ($rk+1)) {
                $row = [];
                foreach ($r as $k => $v) {
                    if ($v == null or $v == '') {
                        $row[] = 'null';
                        continue;
                    }
                    $row[] = "'" . pg_escape_string($v) . "'::" . $casts[$k];
                }
                $values[] = '(' . implode(',', $row) . ')';
            }
        }
        $sql = "SELECT*FROM(VALUES " . implode(',', $values) . ") t (" . implode(',', $dataKeys) . ")";
        return $sql;
    }

    protected function getStoragePath() {
        $filep = explode('/', __DIR__);
        $filep[count($filep) - 1] = 'storage';
        $filep[] = 'nextpro';
        return implode('/', $filep) . '/';
    }

    protected function generate_file_name(UploadedFile $file) {
        return uniqid() . time() . '.' . $file->getClientOriginalExtension();
    }

    protected function replaceWinSlash($str) {
        return str_replace('\\', '/', $str);
    }
    protected function send_to_erp(string $function, array $data) {
        $url = 'https://test.bgbyte.com/employees/external/getFromNextPro';
        //$url = 'http://erp.com/employees/external/getFromNextPro';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'function' => $function,
            'data' => json_encode($data),
            'key' => 'Xm7KhUbHmqe9BBfSOGX6h0AhzQHjMMpV'
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        $return = json_decode($response, true);
        if(json_last_error() and getenv('APPLICATION_ENV') === 'development'){
            dump($response);
            exit;
        }
        return $return;
    }
    protected function RemoveSpecialChar($str) { // Изчиства тотално стринг
        // Using str_replace() function
        // to replace the word
        $arrReplace = ['\'','"','@','<', '>','#','$','/','?','%','^','&','*','(',')','[',']','{','}','|','='];
        return str_replace($arrReplace, ' ', $str); // Returning the result

    }

    protected function ediConnect(){
        return \sqlsrv_connect('10.22.1.222,1433', ['Database' => 'BORA_EDI', 'UID' => 'Nextpro', 'PWD' => 'Ap9m&i4eu!82e~Ds', 'CharacterSet' => 'UTF-8']);
    }

    protected function biConnect(){
        return \sqlsrv_connect('10.22.1.222,1433', ['Database' => 'BORA_BI_MEBELI_VIDENOV', 'UID' => 'Nextpro', 'PWD' => 'Ap9m&i4eu!82e~Ds', 'CharacterSet' => 'UTF-8']);
    }
    protected function rrmdir($dir) { // изтрива първо файловете в папката а после и папката
        foreach(glob($dir . '/*') as $file) {
            if(is_dir($file))
                rrmdir($file);
            else
                unlink($file);
        }
        rmdir($dir);
    }
    protected function flat_array(array $array) { // ако арея е мулти го прави на 1 ниво за рекурсии
        $flat = array();
        $count = 0;
        foreach ($array as $k => $value) {
            if (is_array($value)) {
                $flat = array_merge($flat, $this->flat_array($value));
                continue;
            }
            $flat[$count][$k] = $value;
        }
        return $flat;
    }
}


