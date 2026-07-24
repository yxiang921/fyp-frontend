<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\PatrolAlertNotification;
use App\Models\Alert;
use App\Models\Detection;
use App\Models\MissingPerson;
use App\Models\Camera;
use App\Models\Timeline;
class mailtest extends Controller
{
    public function test(){
        
            Mail::to('xiangf35@gmail.com')->send(
                // dummy content
                
            );
            return 'Email sent successfully';
    }
}
