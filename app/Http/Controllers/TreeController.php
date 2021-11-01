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
        set_time_limit(0);

        if ($request->ajax()) {
            $this->getChartJSON();
        } else {
            // $start_date     = $request->input('startDate', Carbon::today()->subMonth()->toDateString());
            // $end_date   = $request->input('endDate', Carbon::today()->toDateString());
            $start_date     = '2020-12-01';
            $end_date   = '2020-12-31';

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
                `kriot_yomi`.`dif_sons`               AS `dif_sons`
                FROM  `monim`
                LEFT JOIN `kriot_yomi` ON `monim`.`mone` = `kriot_yomi`.`mone`
                LEFT JOIN `customers` ON `monim`.`neches` = `customers`.`neches`
                GROUP BY `monim`.`mone`, `monim`.`neches`, `customers`.`address`,`kriot_yomi`.`dif_sons`
            ";
            $mone_arr       = DB::select($sql);
            $this->mone_arr = array_combine(array_column($mone_arr, 'mone'), $mone_arr);
            if ($request->has('mone_av')) {
                // First get recursive tree model of mone nodes
                $mone_av = $request->input('mone_av');
                if ($mone_av)
                    $mones = Mones::where('mone', $mone_av);
                else
                    $mones = Mones::whereNull('mone_av');
                $mones = $mones->with('_children')->get();
                if (isset($mones[0])) {
                    $mones = $mones[0];
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
        $mone->qty        = $result->qty;
        $mone->address    = $result->address;
        $mone->real_qty   = $result->real_qty;
        $mone->per_cent   = $result->per_cent;
        $mone->delta   = $result->delta;
        $mone->dif_sons   = $result->dif_sons;
        if ($mone->_children->isNotEmpty())
            foreach ($mone->_children as $each)
                $this->getTree($each);
    }

    public function getChartJSON(Request $request)
    {
        $mone = $request->input('mone');
        $mone = 14282937;
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $day_step = $request->input('day_step');
        if ($day_step == 'daily') {
            $sql = "SELECT DATE(day_date) AS date, real_qty, qty, delta FROM view_yomi WHERE mone = '{$mone}' AND day_date > '{$start_date}' AND day_date < '{$end_date}' ORDER BY day_date";
            $result = DB::select($sql);
        } else if ($day_step == 'hourly') {
            $sql = "
                SELECT REPLACE(a.day_date, '00:00:00','00:00:00') AS date, a.h00 AS qty, b.qty as r_qty FROM transmissions AS a
                LEFT JOIN (SELECT DISTINCT mone, reading_day, qty FROM kriot_ratsif WHERE reading_date LIKE '% 00%') AS b ON a.day_date=b.reading_day
                WHERE a.mone = '{$mone}' AND a.total>0 AND a.day_date > '{$start_date}' AND a.day_date < '{$end_date}' AND b.mone = '{$mone}'
                UNION
                SELECT REPLACE(a.day_date, '00:00:00','01:00:00') AS date, a.h01 AS qty, b.qty as r_qty FROM transmissions AS a
                LEFT JOIN (SELECT DISTINCT mone, reading_day, qty FROM kriot_ratsif WHERE reading_date LIKE '% 01%') AS b ON a.day_date=b.reading_day
                WHERE a.mone = '{$mone}' AND a.total>0 AND a.day_date > '{$start_date}' AND a.day_date < '{$end_date}' AND b.mone = '{$mone}'
                UNION
                SELECT REPLACE(a.day_date, '00:00:00','02:00:00') AS date, a.h02 AS qty, b.qty as r_qty FROM transmissions AS a
                LEFT JOIN (SELECT DISTINCT mone, reading_day, qty FROM kriot_ratsif WHERE reading_date LIKE '% 02%') AS b ON a.day_date=b.reading_day
                WHERE a.mone = '{$mone}' AND a.total>0 AND a.day_date > '{$start_date}' AND a.day_date < '{$end_date}' AND b.mone = '{$mone}'
                UNION
                SELECT REPLACE(a.day_date, '00:00:00','03:00:00') AS date, a.h03 AS qty, b.qty as r_qty FROM transmissions AS a
                LEFT JOIN (SELECT DISTINCT mone, reading_day, qty FROM kriot_ratsif WHERE reading_date LIKE '% 03%') AS b ON a.day_date=b.reading_day
                WHERE a.mone = '{$mone}' AND a.total>0 AND a.day_date > '{$start_date}' AND a.day_date < '{$end_date}' AND b.mone = '{$mone}'
                UNION
                SELECT REPLACE(a.day_date, '00:00:00','04:00:00') AS date, a.h04 AS qty, b.qty as r_qty FROM transmissions AS a
                LEFT JOIN (SELECT DISTINCT mone, reading_day, qty FROM kriot_ratsif WHERE reading_date LIKE '% 04%') AS b ON a.day_date=b.reading_day
                WHERE a.mone = '{$mone}' AND a.total>0 AND a.day_date > '{$start_date}' AND a.day_date < '{$end_date}' AND b.mone = '{$mone}'
                UNION
                SELECT REPLACE(a.day_date, '00:00:00','05:00:00') AS date, a.h05 AS qty, b.qty as r_qty FROM transmissions AS a
                LEFT JOIN (SELECT DISTINCT mone, reading_day, qty FROM kriot_ratsif WHERE reading_date LIKE '% 05%') AS b ON a.day_date=b.reading_day
                WHERE a.mone = '{$mone}' AND a.total>0 AND a.day_date > '{$start_date}' AND a.day_date < '{$end_date}' AND b.mone = '{$mone}'
                UNION
                SELECT REPLACE(a.day_date, '00:00:00','06:00:00') AS date, a.h06 AS qty, b.qty as r_qty FROM transmissions AS a
                LEFT JOIN (SELECT DISTINCT mone, reading_day, qty FROM kriot_ratsif WHERE reading_date LIKE '% 06%') AS b ON a.day_date=b.reading_day
                WHERE a.mone = '{$mone}' AND a.total>0 AND a.day_date > '{$start_date}' AND a.day_date < '{$end_date}' AND b.mone = '{$mone}'
                UNION
                SELECT REPLACE(a.day_date, '00:00:00','07:00:00') AS date, a.h07 AS qty, b.qty as r_qty FROM transmissions AS a
                LEFT JOIN (SELECT DISTINCT mone, reading_day, qty FROM kriot_ratsif WHERE reading_date LIKE '% 07%') AS b ON a.day_date=b.reading_day
                WHERE a.mone = '{$mone}' AND a.total>0 AND a.day_date > '{$start_date}' AND a.day_date < '{$end_date}' AND b.mone = '{$mone}'
                UNION
                SELECT REPLACE(a.day_date, '00:00:00','08:00:00') AS date, a.h08 AS qty, b.qty as r_qty FROM transmissions AS a
                LEFT JOIN (SELECT DISTINCT mone, reading_day, qty FROM kriot_ratsif WHERE reading_date LIKE '% 08%') AS b ON a.day_date=b.reading_day
                WHERE a.mone = '{$mone}' AND a.total>0 AND a.day_date > '{$start_date}' AND a.day_date < '{$end_date}' AND b.mone = '{$mone}'
                UNION
                SELECT REPLACE(a.day_date, '00:00:00','09:00:00') AS date, a.h09 AS qty, b.qty as r_qty FROM transmissions AS a
                LEFT JOIN (SELECT DISTINCT mone, reading_day, qty FROM kriot_ratsif WHERE reading_date LIKE '% 09%') AS b ON a.day_date=b.reading_day
                WHERE a.mone = '{$mone}' AND a.total>0 AND a.day_date > '{$start_date}' AND a.day_date < '{$end_date}' AND b.mone = '{$mone}'
                UNION
                SELECT REPLACE(a.day_date, '00:00:00','10:00:00') AS date, a.h10 AS qty, b.qty as r_qty FROM transmissions AS a
                LEFT JOIN (SELECT DISTINCT mone, reading_day, qty FROM kriot_ratsif WHERE reading_date LIKE '% 10%') AS b ON a.day_date=b.reading_day
                WHERE a.mone = '{$mone}' AND a.total>0 AND a.day_date > '{$start_date}' AND a.day_date < '{$end_date}' AND b.mone = '{$mone}'
                UNION
                SELECT REPLACE(a.day_date, '00:00:00','11:00:00') AS date, a.h11 AS qty, b.qty as r_qty FROM transmissions AS a
                LEFT JOIN (SELECT DISTINCT mone, reading_day, qty FROM kriot_ratsif WHERE reading_date LIKE '% 11%') AS b ON a.day_date=b.reading_day
                WHERE a.mone = '{$mone}' AND a.total>0 AND a.day_date > '{$start_date}' AND a.day_date < '{$end_date}' AND b.mone = '{$mone}'
                UNION
                SELECT REPLACE(a.day_date, '00:00:00','12:00:00') AS date, a.h12 AS qty, b.qty as r_qty FROM transmissions AS a
                LEFT JOIN (SELECT DISTINCT mone, reading_day, qty FROM kriot_ratsif WHERE reading_date LIKE '% 12%') AS b ON a.day_date=b.reading_day
                WHERE a.mone = '{$mone}' AND a.total>0 AND a.day_date > '{$start_date}' AND a.day_date < '{$end_date}' AND b.mone = '{$mone}'
                UNION
                SELECT REPLACE(a.day_date, '00:00:00','13:00:00') AS date, a.h13 AS qty, b.qty as r_qty FROM transmissions AS a
                LEFT JOIN (SELECT DISTINCT mone, reading_day, qty FROM kriot_ratsif WHERE reading_date LIKE '% 13%') AS b ON a.day_date=b.reading_day
                WHERE a.mone = '{$mone}' AND a.total>0 AND a.day_date > '{$start_date}' AND a.day_date < '{$end_date}' AND b.mone = '{$mone}'
                UNION
                SELECT REPLACE(a.day_date, '00:00:00','14:00:00') AS date, a.h14 AS qty, b.qty as r_qty FROM transmissions AS a
                LEFT JOIN (SELECT DISTINCT mone, reading_day, qty FROM kriot_ratsif WHERE reading_date LIKE '% 14%') AS b ON a.day_date=b.reading_day
                WHERE a.mone = '{$mone}' AND a.total>0 AND a.day_date > '{$start_date}' AND a.day_date < '{$end_date}' AND b.mone = '{$mone}'
                UNION
                SELECT REPLACE(a.day_date, '00:00:00','15:00:00') AS date, a.h15 AS qty, b.qty as r_qty FROM transmissions AS a
                LEFT JOIN (SELECT DISTINCT mone, reading_day, qty FROM kriot_ratsif WHERE reading_date LIKE '% 15%') AS b ON a.day_date=b.reading_day
                WHERE a.mone = '{$mone}' AND a.total>0 AND a.day_date > '{$start_date}' AND a.day_date < '{$end_date}' AND b.mone = '{$mone}'
                UNION
                SELECT REPLACE(a.day_date, '00:00:00','16:00:00') AS date, a.h16 AS qty, b.qty as r_qty FROM transmissions AS a
                LEFT JOIN (SELECT DISTINCT mone, reading_day, qty FROM kriot_ratsif WHERE reading_date LIKE '% 16%') AS b ON a.day_date=b.reading_day
                WHERE a.mone = '{$mone}' AND a.total>0 AND a.day_date > '{$start_date}' AND a.day_date < '{$end_date}' AND b.mone = '{$mone}'
                UNION
                SELECT REPLACE(a.day_date, '00:00:00','17:00:00') AS date, a.h17 AS qty, b.qty as r_qty FROM transmissions AS a
                LEFT JOIN (SELECT DISTINCT mone, reading_day, qty FROM kriot_ratsif WHERE reading_date LIKE '% 17%') AS b ON a.day_date=b.reading_day
                WHERE a.mone = '{$mone}' AND a.total>0 AND a.day_date > '{$start_date}' AND a.day_date < '{$end_date}' AND b.mone = '{$mone}'
                UNION
                SELECT REPLACE(a.day_date, '00:00:00','18:00:00') AS date, a.h18 AS qty, b.qty as r_qty FROM transmissions AS a
                LEFT JOIN (SELECT DISTINCT mone, reading_day, qty FROM kriot_ratsif WHERE reading_date LIKE '% 18%') AS b ON a.day_date=b.reading_day
                WHERE a.mone = '{$mone}' AND a.total>0 AND a.day_date > '{$start_date}' AND a.day_date < '{$end_date}' AND b.mone = '{$mone}'
                UNION
                SELECT REPLACE(a.day_date, '00:00:00','19:00:00') AS date, a.h19 AS qty, b.qty as r_qty FROM transmissions AS a
                LEFT JOIN (SELECT DISTINCT mone, reading_day, qty FROM kriot_ratsif WHERE reading_date LIKE '% 19%') AS b ON a.day_date=b.reading_day
                WHERE a.mone = '{$mone}' AND a.total>0 AND a.day_date > '{$start_date}' AND a.day_date < '{$end_date}' AND b.mone = '{$mone}'
                UNION
                SELECT REPLACE(a.day_date, '00:00:00','20:00:00') AS date, a.h20 AS qty, b.qty as r_qty FROM transmissions AS a
                LEFT JOIN (SELECT DISTINCT mone, reading_day, qty FROM kriot_ratsif WHERE reading_date LIKE '% 20%') AS b ON a.day_date=b.reading_day
                WHERE a.mone = '{$mone}' AND a.total>0 AND a.day_date > '{$start_date}' AND a.day_date < '{$end_date}' AND b.mone = '{$mone}'
                UNION
                SELECT REPLACE(a.day_date, '00:00:00','21:00:00') AS date, a.h21 AS qty, b.qty as r_qty FROM transmissions AS a
                LEFT JOIN (SELECT DISTINCT mone, reading_day, qty FROM kriot_ratsif WHERE reading_date LIKE '% 21%') AS b ON a.day_date=b.reading_day
                WHERE a.mone = '{$mone}' AND a.total>0 AND a.day_date > '{$start_date}' AND a.day_date < '{$end_date}' AND b.mone = '{$mone}'
                UNION
                SELECT REPLACE(a.day_date, '00:00:00','22:00:00') AS date, a.h22 AS qty, b.qty as r_qty FROM transmissions AS a
                LEFT JOIN (SELECT DISTINCT mone, reading_day, qty FROM kriot_ratsif WHERE reading_date LIKE '% 22%') AS b ON a.day_date=b.reading_day
                WHERE a.mone = '{$mone}' AND a.total>0 AND a.day_date > '{$start_date}' AND a.day_date < '{$end_date}' AND b.mone = '{$mone}'
                UNION
                SELECT REPLACE(a.day_date, '00:00:00','23:00:00') AS date, a.h23 AS qty, b.qty as r_qty FROM transmissions AS a
                LEFT JOIN (SELECT DISTINCT mone, reading_day, qty FROM kriot_ratsif WHERE reading_date LIKE '% 23%') AS b ON a.day_date=b.reading_day
                WHERE a.mone = '{$mone}' AND a.total>0 AND a.day_date > '{$start_date}' AND a.day_date < '{$end_date}' AND b.mone = '{$mone}'
                ORDER BY date
                ";
            $result = DB::select($sql);
        }
        return response()->json($result);
    }
}
