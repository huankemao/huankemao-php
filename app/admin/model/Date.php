<?php
/**
 * Created by Shy
 * Date 2020/12/18
 * Time 17:01
 */


namespace app\admin\model;


class Date
{

    /**
     * User:Shy
     * @param $sdefaultDate
     * @return string
     */
    static function weeks($sdefaultDate)
    {
        $first = 1;
        $w = date('w', strtotime($sdefaultDate));
        $week_start = date('Y-m-d', strtotime("$sdefaultDate -" . ($w ? $w - $first : 6) . ' days'));
        $week_end = date('Y-m-d', strtotime("$week_start +6 days"));
        return "$week_start/$week_end";
    }
    
    /**
     * User:Shy
     * @param $sdefaultDate
     * @return string
     */
    static function months($sdefaultDate)
    {
        $firstday = date('Y-m-01', strtotime($sdefaultDate));
        $lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));
        return "$firstday/$lastday";
    }

    /**
     * User:Shy
     * 获取指定日期之间的各个天
     * @param $start_time
     * @param $end_time
     * @return array
     */

    static function getDateFromRange($start_time, $end_time)
    {
        $start_time = strtotime($start_time);
        $end_time = strtotime($end_time);
        $days = ($end_time - $start_time) / 86400 + 1;
        $date = array();
        for ($i = 0; $i < $days; $i++) {
            $date[] = date('Y-m-d', $start_time + (86400 * $i));
        }
        return $date;
    }

    /**
     * User:Shy
     * 获取指定日期之间的各个周
     * @param $sdate
     * @param $edate
     * @return array
     */
    static function get_weeks($sdate, $edate)
    {
        $range_arr = array();
        // 计算各个周的起始时间
        do {
            $weekinfo = self::get_weekinfo_by_date($sdate);
            $end_day = $weekinfo['week_end_day'];
            $start = self::substr_date($weekinfo['week_start_day']);
            $end = self::substr_date($weekinfo['week_end_day']);
            $range = "{$start}/{$end}";
            $range_arr[] = $range;
            $sdate = date('Y-m-d', strtotime($sdate) + 7 * 86400);
        } while ($end_day < $edate);
        return $range_arr;
    }

    /**
     * User:Shy
     * @param $date
     * @return array
     */
    static function get_weekinfo_by_date($date)
    {
        $idx = strftime("%u", strtotime($date));
        $mon_idx = $idx - 1;
        $sun_idx = $idx - 7;
        return array(
            'week_start_day' => strftime('%Y-%m-%d', strtotime($date) - $mon_idx * 86400),
            'week_end_day' => strftime('%Y-%m-%d', strtotime($date) - $sun_idx * 86400),
        );
    }

    /**
     * User:Shy
     * @param $date
     * @return bool|false|string
     */
    static function substr_date($date)
    {
        if (!$date) return FALSE;
        return date('Y-m-d', strtotime($date));
    }

    /**
     * User:Shy
     * 获取指定日期之间的各个月
     * @param $sdate
     * @param $edate
     * @return array
     */
    static function get_months($sdate, $edate)
    {
        $range_arr = array();
        do {
            $monthinfo = self::get_monthinfo_by_date($sdate);
            $end_day = $monthinfo['month_end_day'];
            $start = self::substr_date($monthinfo['month_start_day']);
            $end = self::substr_date($monthinfo['month_end_day']);
            $range = "{$start}/{$end}";
            $range_arr[] = $range;
            $sdate = date('Y-m-d', strtotime($sdate . '+1 month'));
        } while ($end_day < $edate);
        return $range_arr;
    }

    /**
     * User:Shy
     * 根据指定日期获取所在月的起始时间和结束时间
     */
    static function get_monthinfo_by_date($date)
    {
        $timestamp = strtotime($date);
        $mdays = date('t', $timestamp);
        return array(
            'month_start_day' => date('Y-m-1', $timestamp),
            'month_end_day' => date('Y-m-' . $mdays, $timestamp)
        );
    }

}