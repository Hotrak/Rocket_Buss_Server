<?php
namespace App;

class DriverManager
{
    private $date;
    private $drivers;
    private $driversCount;
    private $counterDrivers = 0;

    private $currentScheduleCreateInfo;
    private $isTodayScheduleCreateInfo =false;
    private $newUnWorkedDrivers = [];

    private $workedDrivers = [];
    private $unWorkerCount = 0;
    private $unWorkedDrivers;

    public function __construct($date)
    {
        $this->date= $date;
        $this->drivers = Driver::all();
        $this->driversCount = count($this->drivers);

//        $this->unWorkedDrivers = $this->setDriversCounter();
        $this->currentScheduleCreateInfo = $this->setDriversCounter();


        $this->unWorkedDrivers = [];
        if(isset($this->currentScheduleCreateInfo)){
            $this->unWorkedDrivers = unserialize($this->currentScheduleCreateInfo->schedule_holidays_drivers);
        }

    }

    public function getDriver($timeDrive,$time){
        $isFind = false;
        $loop = 0;
        $driver = new Driver();
        $counterDrivers = $this->counterDrivers;
        $dayOfWeek = date("N",strtotime($this->date));
        $currentDriver = -1;
        $isUnWorkedDriver = false;

        while (!$isFind){

            if($this->driversCount*2<=$loop)
                return -1;


            if($this->unWorkerCount < count($this->unWorkedDrivers )&& !$this->isTodayScheduleCreateInfo ){
                $currentDriver =  $this->unWorkedDrivers[$this->unWorkerCount];
//                        $newUnWorkedDrivers[] =  $currentDriver;
                $this->unWorkerCount++;
                $isUnWorkedDriver = true;
                $isFind = true;
            }
            else if($counterDrivers < count($this->drivers)){

                if($this->drivers[$counterDrivers]->week_end != $dayOfWeek ){
                    $this->workedDrivers[] = $this->drivers[$counterDrivers]->id;
                    $currentDriver = $this->drivers[$counterDrivers]->id;
                    if($driver->checkMedExam($currentDriver,$this->date))
                        if($driver->checkWorkTime($currentDriver,$this->date,$timeDrive))
                            if($driver->checkFreeTime($currentDriver,$this->date,$time,$timeDrive))
                                $isFind = true;

                    if(!$isFind)
                        $counterDrivers++;
                }else{
                    $this->newUnWorkedDrivers[] = $this->drivers[$counterDrivers]->id;
                    $counterDrivers++;
                }
            }
            else
                $counterDrivers = 0;

            $loop++;
        }

        if(!$isUnWorkedDriver)
            $counterDrivers++;

        $this->counterDrivers = $counterDrivers;

        return $currentDriver;


    }

    private function setDriversCounter(){
        $scheduleCreateInfo  = new ScheduleCreateInfo();
        $currentScheduleCreateInfo = $scheduleCreateInfo->getByDate($this->date);

        if(isset($currentScheduleCreateInfo)){
//            $this->workedDrivers = unserialize($currentScheduleCreateInfo->schedule_drivers);
            $this->unWorkedDrivers = unserialize($currentScheduleCreateInfo->schedule_holidays_drivers);

//            $countWorkedDrivers = count($this->workedDrivers);
            $counterDrivers = Schedule::where('date_start','=',$this->date)
                ->select('driver_id')
                ->orderByDesc('id')
                ->first();
            if(isset($counterDrivers)){
                $counterDrivers = $counterDrivers->driver_id+1;
                $count = 0;
                foreach ($this->drivers as $item){
                    if($item->id == $counterDrivers){
                        $this->counterDrivers = $count;
                        break;
                    }
                    $count++;
                }
            }
//                dd($counterDrivers);


            if($currentScheduleCreateInfo->schedule_date == $this->date){
                $this->isTodayScheduleCreateInfo = true;
            }
            else{
                $this->workedDrivers=[];
            }


            return $currentScheduleCreateInfo;
        }
        return null;
    }

    public function saveChoseInfo(){
        if($this->isTodayScheduleCreateInfo)
            $scheduleCreateInfo = $this->currentScheduleCreateInfo;
        else
            $scheduleCreateInfo = new ScheduleCreateInfo;
        $scheduleCreateInfo->schedule_date = $this->date;
        $scheduleCreateInfo->schedule_drivers = ""; //serialize($this->workedDrivers)
        $scheduleCreateInfo->schedule_holidays_drivers = serialize(array_unique($this->newUnWorkedDrivers));
        $scheduleCreateInfo->save();
    }

}
