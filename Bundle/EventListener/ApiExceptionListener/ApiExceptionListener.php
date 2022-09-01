<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2020/1/2
 */
namespace PHPZlc\PHPZlc\Bundle\EventListener\ApiExceptionListener;

use PHPZlc\PHPZlc\Abnormal\Errors;
use PHPZlc\PHPZlc\Responses\Responses;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiExceptionListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var RequestStack
     */
    private $request;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->request = $this->container->get('request_stack');
    }

    public function onKernelException(ExceptionEvent $event)
    {
        // API 主动抛出返回响应
        if($event->getThrowable() instanceof ApiException){
            $event->setResponse(Responses::error(
                $event->getThrowable()->getMessage(),
                $event->getThrowable()->getCode(),
                $event->getThrowable()->getData(),
                $event->getThrowable()->getType()
            ));
        }else{
            // 生产模式 隐藏500错误
            if($_ENV['APP_ENV'] == 'prod'){
                $event->setResponse(Errors::exceptionError($event->getThrowable(), false, $this->request));
            }else{
                throw $event->getThrowable();
            }
        }
    }
}