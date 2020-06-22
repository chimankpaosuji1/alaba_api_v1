<?php

namespace App\Listeners;

use App\Events\SendPasswordResetLinkEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendPasswordResetMail
{


    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        //
        $data['reset_link'] = 'https://alabamarket.com/auth/renew/'.$event->passwordReset->token;
        $data['user_name'] = $event->passwordReset->user_name;
        $data['reason'] = $event->passwordReset->reason;
        $data['year'] = date('Y');
        $this->msg = ($data['reason'] == 'account locked')?'Account Temporary Locked':'Reset Password Request';
        $this->user_name = $event->passwordReset->user_name;
        $this->email = $event->passwordReset->email;
            Mail::send('passwordreset', $data, function($message) {
                $message->to($this->email,$this->user_name);
                $message->subject($this->msg);
            });
    }
}
