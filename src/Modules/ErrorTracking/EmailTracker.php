<?php

namespace iEducar\Modules\ErrorTracking;

use App\Mail\ErrorTrackerMail;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler;
use Throwable;

class EmailTracker implements Tracker
{
    public function notify(Throwable $exception, $data = [])
    {
        $subject = '[Erro inesperado] i-Educar - ' . config('app.name');

        $to = $this->getRecipient();

        try {
            $e = FlattenException::create($exception);

            $handler = new ExceptionHandler();

            $html = $handler->getHtml($e);

            $mail = new ErrorTrackerMail($to, $subject, $html);

            Mail::send($mail);
        } catch (Throwable $throwable) {
            //
        }
    }

    private function getRecipient()
    {
        return $GLOBALS['coreExt']['Config']->modules->error->email_recipient;
    }
}
