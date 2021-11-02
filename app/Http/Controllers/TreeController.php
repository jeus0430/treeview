<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\KriotYomi;
use App\Models\Mones;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
class TreeController extends Controller
{
    public function index(Request $request)
    {
        // $request->mone
        set_time_limit(0);

        if ($request->ajax()) {
            $this->getChartJSON();
        } else {
            $start_date     = $request->input('startDate', '2020-12-01');
            $end_date   = $request->input('endDate', '2020-12-31');
            if ($request->has('mone_av')) {
                // Get parent mones with raw sql query from view table
                // $sql = "
                //     SELECT c.mone, b.address FROM
                //     (SELECT mone_av FROM monim WHERE mone_av IS NOT NULL GROUP BY mone_av) AS a
                //     LEFT JOIN monim c ON c.mone = a.mone_av
                //     LEFT JOIN customers b ON b.neches = c.neches";
                // $parents = DB::select($sql);

                // Get total mone array with relavant info fields with raw sql query from moim table
                $sql = "
                    SELECT
                    AVG(`kriot_yomi`.`qty`)            AS `qty`,
                    AVG(`kriot_yomi`.`real_qty`)       AS `real_qty`,
                    AVG(`kriot_yomi`.`delta`)          AS `delta`,
                    AVG(`kriot_yomi`.`per_cent`)       AS `per_cent`,
                    `monim`.`mone`                AS `mone`,
                    `monim`.`neches`              AS `neches`,
                    `customers`.`address`            AS `address`,
                    `kriot_yomi`.`dif_sons`               AS `dif_sons`,
                    `monim`.`sivug` AS `sivug`
                    FROM  `monim`
                    LEFT JOIN `kriot_yomi` ON `monim`.`mone` = `kriot_yomi`.`mone`
                    LEFT JOIN `customers` ON `monim`.`neches` = `customers`.`neches`
                    GROUP BY `monim`.`mone`, `monim`.`neches`, `customers`.`address`,`kriot_yomi`.`dif_sons`, `monim`.`sivug`
                ";
                $mone_arr       = DB::select($sql);
                $this->mone_arr = array_combine(array_column($mone_arr, 'mone'), $mone_arr);

                // First get recursive tree model of mone nodes
                $mone_av = $request->input('mone_av');
                if ($mone_av)
                    $mones = Mones::where('mone', $mone_av);
                else
                    $mones = Mones::whereNull('mone_av');
                $mones = $mones->with('_children')->get();
                if(isset($mones[0])){
                    $mones = $mones[0]->toArray();

                    // Then append relavent info field to each nodes by traversing each nodes recursively
                    $this->getTree($mones);
                    // Finally convert tree model into string with json and base64 hash algorithm.
                    $mones = base64_encode(json_encode($mones));
                    return view('treebox', compact('mones', 'mone_av', 'start_date', 'end_date'));
                }
                return view('treebox', compact('mone_av', 'start_date', 'end_date'));
                // return view('treebox', compact('mones', 'parents', 'mone_av', 'start_date', 'end_date'));
            } else {
                $mone_av = 0;
                // return view('treebox', compact('parents', 'mone_av', 'start_date', 'end_date'));
                return view('treebox', compact('mone_av', 'start_date', 'end_date'));
            }
        }
    }

    public function getTree(&$mone)
    {
        $result = $this->mone_arr[$mone['mone']];
        $mone['qty']        = $result->qty;
        $mone['address']    = $result->address;
        $mone['real_qty']   = $result->real_qty;
        $mone['per_cent']   = $result->per_cent;
        $mone['delta']   = $result->delta;
        $mone['dif_sons']   = $result->dif_sons;
        $mone['sivug']   = $result->sivug;

        if ((int)$mone['sivug']) {
            $v_children = [];
            foreach($mone['_children'] as $each)
                if (count($each['_children'])) {
                    foreach($each['_children'] as $each_c)
                    array_push($v_children, $each_c);
                }
                $mone['_children'] = $v_children;
        }

        if (count($mone['_children']))
            foreach($mone['_children'] as $each)
                $this->getTree($each);
    }

    public function getChartJSON(Request $request)
    {
        $mone = $request->input('mone');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $day_step = $request->input('day_step');
        if ($day_step == 'daily') {
            $sql = "SELECT DATE(day_date) AS date, real_qty, qty, delta FROM view_yomi WHERE mone = '{$mone}' AND day_date > '{$start_date}' AND day_date < '{$end_date}' ORDER BY day_date";
            $sql1 = "SELECT qty, reading_date FROM kriot_ratsif WHERE reading_date > '{$start_date}' AND reading_date < '{$end_date}' AND mone= {$mone} ORDER BY reading_date ASC ";
            $result = DB::select($sql);
            $result1 = DB::select($sql1);
            $result = array (
                'org' => $result,
                'new' => $result1
            );
        } else if ($day_step == 'hourly') {
            $sql = "
                SELECT REPLACE(day_date, '00:00:00','00:00:00') AS date, h00 AS qty FROM transmissions
                WHERE mone = '{$mone}' AND total>0 AND day_date > '{$start_date}' AND day_date < '{$end_date}'
                UNION
                SELECT REPLACE(day_date, '00:00:00','01:00:00'), h01 FROM transmissions
                WHERE mone = '{$mone}' AND total>0 AND day_date > '{$start_date}' AND day_date < '{$end_date}'
                UNION
                SELECT REPLACE(day_date, '00:00:00','02:00:00'), h02 FROM transmissions
                WHERE mone = '{$mone}' AND total>0 AND day_date > '{$start_date}' AND day_date < '{$end_date}'
                UNION
                SELECT REPLACE(day_date, '00:00:00','03:00:00'), h03 FROM transmissions
                WHERE mone = '{$mone}' AND total>0 AND day_date > '{$start_date}' AND day_date < '{$end_date}'
                UNION
                SELECT REPLACE(day_date, '00:00:00','04:00:00'), h04 FROM transmissions
                WHERE mone = '{$mone}' AND total>0 AND day_date > '{$start_date}' AND day_date < '{$end_date}'
                UNION
                SELECT REPLACE(day_date, '00:00:00','05:00:00'), h05 FROM transmissions
                WHERE mone = '{$mone}' AND total>0 AND day_date > '{$start_date}' AND day_date < '{$end_date}'
                UNION
                SELECT REPLACE(day_date, '00:00:00','06:00:00'), h06 FROM transmissions
                WHERE mone = '{$mone}' AND total>0 AND day_date > '{$start_date}' AND day_date < '{$end_date}'
                UNION
                SELECT REPLACE(day_date, '00:00:00','07:00:00'), h07 FROM transmissions
                WHERE mone = '{$mone}' AND total>0 AND day_date > '{$start_date}' AND day_date < '{$end_date}'
                UNION
                SELECT REPLACE(day_date, '00:00:00','08:00:00'), h08 FROM transmissions
                WHERE mone = '{$mone}' AND total>0 AND day_date > '{$start_date}' AND day_date < '{$end_date}'
                UNION
                SELECT REPLACE(day_date, '00:00:00','09:00:00'), h09 FROM transmissions
                WHERE mone = '{$mone}' AND total>0 AND day_date > '{$start_date}' AND day_date < '{$end_date}'
                UNION
                SELECT REPLACE(day_date, '00:00:00','10:00:00'), h10 FROM transmissions
                WHERE mone = '{$mone}' AND total>0 AND day_date > '{$start_date}' AND day_date < '{$end_date}'
                UNION
                SELECT REPLACE(day_date, '00:00:00','11:00:00'), h11 FROM transmissions
                WHERE mone = '{$mone}' AND total>0 AND day_date > '{$start_date}' AND day_date < '{$end_date}'
                UNION
                SELECT REPLACE(day_date, '00:00:00','12:00:00'), h12 FROM transmissions
                WHERE mone = '{$mone}' AND total>0 AND day_date > '{$start_date}' AND day_date < '{$end_date}'
                UNION
                SELECT REPLACE(day_date, '00:00:00','13:00:00'), h13 FROM transmissions
                WHERE mone = '{$mone}' AND total>0 AND day_date > '{$start_date}' AND day_date < '{$end_date}'
                UNION
                SELECT REPLACE(day_date, '00:00:00','14:00:00'), h14 FROM transmissions
                WHERE mone = '{$mone}' AND total>0 AND day_date > '{$start_date}' AND day_date < '{$end_date}'
                UNION
                SELECT REPLACE(day_date, '00:00:00','15:00:00'), h15 FROM transmissions
                WHERE mone = '{$mone}' AND total>0 AND day_date > '{$start_date}' AND day_date < '{$end_date}'
                UNION
                SELECT REPLACE(day_date, '00:00:00','16:00:00'), h16 FROM transmissions
                WHERE mone = '{$mone}' AND total>0 AND day_date > '{$start_date}' AND day_date < '{$end_date}'
                UNION
                SELECT REPLACE(day_date, '00:00:00','17:00:00'), h17 FROM transmissions
                WHERE mone = '{$mone}' AND total>0 AND day_date > '{$start_date}' AND day_date < '{$end_date}'
                UNION
                SELECT REPLACE(day_date, '00:00:00','18:00:00'), h18 FROM transmissions
                WHERE mone = '{$mone}' AND total>0 AND day_date > '{$start_date}' AND day_date < '{$end_date}'
                UNION
                SELECT REPLACE(day_date, '00:00:00','19:00:00'), h19 FROM transmissions
                WHERE mone = '{$mone}' AND total>0 AND day_date > '{$start_date}' AND day_date < '{$end_date}'
                UNION
                SELECT REPLACE(day_date, '00:00:00','20:00:00'), h20 FROM transmissions
                WHERE mone = '{$mone}' AND total>0 AND day_date > '{$start_date}' AND day_date < '{$end_date}'
                UNION
                SELECT REPLACE(day_date, '00:00:00','21:00:00'), h21 FROM transmissions
                WHERE mone = '{$mone}' AND total>0 AND day_date > '{$start_date}' AND day_date < '{$end_date}'
                UNION
                SELECT REPLACE(day_date, '00:00:00','22:00:00'), h22 FROM transmissions
                WHERE mone = '{$mone}' AND total>0 AND day_date > '{$start_date}' AND day_date < '{$end_date}'
                UNION
                SELECT REPLACE(day_date, '00:00:00','23:00:00'), h23 FROM transmissions
                WHERE mone = '{$mone}' AND total>0 AND day_date > '{$start_date}' AND day_date < '{$end_date}'
                ORDER BY date";
            $result = DB::select($sql);
        }
        return response()->json($result);
    }
}
