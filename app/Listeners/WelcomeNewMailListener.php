<?php

namespace App\Listeners;

use App\Events\NewCustomerHasRegisteredEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
class WelcomeNewMailListener
{

    public function handle(NewCustomerHasRegisteredEvent $event)
    {
        //
        $data['activation_link'] = 'https://alabamarket.com/auth/confirmation/'.$event->user->token;
        $data['fname'] = $event->user->name;
        $data['year'] = date('Y');
        $this->fname = $event->user->name;
        $this->email = $event->user->email;

            Mail::send('activation_email', $data, function($message) {
                $message->to($this->email,$this->fname);
                $message->subject('Activation Email');
            });
    }
}
