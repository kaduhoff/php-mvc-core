<?php

namespace app\core;

/** 
 * Alertas para a aplicação
 * @author Kadu Hoffmann <kaduhoff@gmail.com>
 * @package app\core 
 */
class Alerts
{
    private static function alert($type, $message, $dismissing = true)
    {
        $dismissingClasses = $dismissing ? ' alert-dismissible fade show ' : '';
        $htmlAlert = '<div class="alert alert-'.$type.$dismissingClasses.'" role="alert">';
        $htmlAlert .= $message;
        if ($dismissing) {
            $htmlAlert .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        }
        $htmlAlert .= '</div>';

        return $htmlAlert;
    }

    public static function getSuccess($message, $dismissing = true): string
    {
        return self::alert('success', $message, $dismissing);
    }

    public static function getDanger($message, $dismissing = true): string
    {
        return self::alert('danger', $message, $dismissing);
    }

    public static function getWarning($message, $dismissing = true): string
    {
        return self::alert('warning', $message, $dismissing);
    }
    
    public static function setSuccess($message, $dismissing = true)
    {
        Application::$app->session->setFlash('success', $message);
    }

    public static function setDanger($message, $dismissing = true)
    {
        Application::$app->session->setFlash('danger', $message);
    }
    

    /**
     * retorna os Alertas em HTML
     * 
     * @return string 
     */
    public static function getFlashAlerts(): string
    {
        $alertsHtml = Application::$app->session->getFlash('success') ? self::getSuccess(Application::$app->session->getFlash('success')) :'';
        $alertsHtml .= Application::$app->session->getFlash('warning') ? self::getWarning(Application::$app->session->getFlash('warning')) :'';
        $alertsHtml .= Application::$app->session->getFlash('danger') ? self::getdanger(Application::$app->session->getFlash('danger')) :'';
        
        return $alertsHtml;
    }
    
}
