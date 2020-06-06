<?php

namespace App;
class CarManager{

    private $date;

    public function __construct($date)
    {
        $this->date = $date;
    }

    public function getCar($timeDrive,$time){

        $isFind = false;
        $cars = Car::whereDate('end_of_inspection','>',$this->date)
            ->whereDate('end_of_insurance','>',$this->date)
            ->where('state','=',1)
            ->get();
        $carsCount= count($cars);
//        dd($cars);
        $car = new Car();

        $carsCounter = 0;
        $currentCar = -1;
        $loop = 0;
        while (!$isFind){

            if($carsCount*2 <= $loop)
                return -1;

            $currentCar = $cars[$carsCounter]->id;

            if($car->checkTime($currentCar,$this->date,$time,$timeDrive)){
                $isFind = true;
            }

            $carsCounter++;
            if($carsCounter>= $carsCount)
                $carsCounter = 0;

            $loop++;
        }

        return $currentCar;
    }
}
