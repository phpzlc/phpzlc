<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2020/1/2
 */
namespace PHPZlc\PHPZlc\Bundle\EventListener\ApiExceptionListener;

use PHPZlc\PHPZlc\Abnormal\Error;
use PHPZlc\PHPZlc\Abnormal\Errors;
use PHPZlc\PHPZlc\Abnormal\PHPZlcException;
use PHPZlc\PHPZlc\Bundle\Controller\SystemBaseController;
use PHPZlc\PHPZlc\Responses\Responses;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiExceptionListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        // API 主动抛出返回响应
        if($event->getThrowable() instanceof PhpZlcApiException){
            $event->setResponse(Responses::error(
                $event->getThrowable()->getMessage(),
                $event->getThrowable()->getCode(),
                $event->getThrowable()->getData(),
                $event->getThrowable()->getType()
            ));
        }

        // 生产模式 隐藏500错误
        if($_ENV['APP_ENV'] == 'prod'){
            $event->setResponse(Responses::exceptionError($event->getThrowable()));
        }
    }
}