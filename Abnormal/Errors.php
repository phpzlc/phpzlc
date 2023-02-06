<?php
namespace PHPZlc\PHPZlc\Abnormal;


use PHPZlc\PHPZlc\Bundle\Controller\SystemBaseController;
use PHPZlc\PHPZlc\Bundle\Service\Log\Log;
use PHPZlc\PHPZlc\Responses\Responses;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Errors
{
    /**
     * @var Error[]
     */
    private static $errors = [];


    /**
     * 设置错误信息
     *
     * @param $msg
     */
    public static function setErrorMessage($msg)
    {
        static::setError(new Error($msg));
    }

    /**
     * 设置错误
     *
     * @param Error $error
     */
    public static function setError(Error $error)
    {
        static::$errors[] = $error;
    }

    /**
     * 得到错误
     *
     * @return bool|Error
     */
    public static function getError()
    {
        return empty(static::$errors) ? false : static::$errors[0];
    }

    /**
     * 得到错误信息
     *
     * @return string
     */
    public static function getErrorMsg()
    {
        return empty(static::getError()) ? '' : static::getError()->msg;
    }


    /**
     * 是否存在错误
     *
     * @return bool
     */
    public static function isExistError()
    {
        return !empty(static::$errors);
    }

    /**
     * 得到全部错误
     *
     * @return Error[]
     */
    public static function getAllError()
    {
        return static::$errors;
    }

    /**
     * 得到全部错误数组
     *
     * @return array
     */
    public static function getAllErrorArray()
    {
        $errors = array();

        foreach (static::$errors as $error){
            if(empty($error->name)) {
                $errors[] = $error->toArray();
            }else{
                $errors[$error->name] = $error->toArray();
            }
        }

        return $errors;
    }

    /**
     * 覆盖错误
     *
     * @param Error $error
     */
    public static function coverError(Error $error)
    {
        array_unshift(static::$errors, $error);
    }

    /**
     * 清空错误
     */
    public static function clearError()
    {
        static::$errors = [];
    }

    /**
     * symfony ValidatorInterface class
     *
     * @param ValidatorInterface $validator
     * @param $class
     * @return bool
     */
    public static function validate(ValidatorInterface $validator, $class)
    {
        if(Errors::isExistError()){
            return false;
        }

        $errors = $validator->validate($class);

        if(count($errors) > 0){
            Errors::setError(new Error($errors->get(0)->getMessage(), null, $errors->get(0)->getPropertyPath(), $errors->get(0)->getInvalidValue()));
            return false;
        }

        return true;
    }

    /**
     * 报错通知
     *
     * @param $logContent
     * @return void
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public static function notificationError($logContent)
    {
        //发送报错邮件给开发者
        if(isset($_ENV['MAILER_DSN']) && isset($_ENV['ERROR_EMAIL_COF'])){
            try {
                $configParams = explode(';', $_ENV['ERROR_EMAIL_COF']);
                $configs = [];
                foreach ($configParams as $configParam){
                    $value = explode(":", $configParam);
                    $configs[$value[0]] = $value[1];
                }

                $to = explode('&', $configs['to']);
                $transport = \Symfony\Component\Mailer\Transport::fromDsn($_ENV['MAILER_DSN']);
                $mailer = new \Symfony\Component\Mailer\Mailer($transport);
                $email = (new \Symfony\Component\Mime\Email())
                    ->from($configs['from'])
                    ->to($to[0])
                    ->subject($configs['subject'])
                    ->text(trim($logContent))
                    ->html(str_replace("\n", "<br>", trim($logContent)));

                if(count($to) > 1){
                    for ($i = 1; $i < count($to); $i++){
                        $email->addTo($to[$i]);
                    }
                }

            }catch (\Exception $exception){
                die('ERROR_EMAIL_COF 格式错误 ' . $exception->getMessage());
            }

            try {
                $mailer->send($email);
            } catch (\Exception $exception){
                Log::writeLog('程序500错误邮件发送失败' . $exception->getMessage());
            }
        }
    }

    /**
     * exception错误
     *
     * @param \Throwable $exception
     * @param bool $isThrow
     * @param RequestStack|null $request
     * @return false|\Symfony\Component\HttpFoundation\JsonResponse|Response|void
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     * @throws \Throwable
     */
    public static function exceptionError(\Throwable $exception, bool $isThrow = true, RequestStack $request = null)
    {
        if($isThrow){
            if(Errors::isExistError()){
                return false;
            }else{
                throw $exception;
            }
        }

        if($exception instanceof NotFoundHttpException) {
            throw new NotFoundHttpException();
        }

        $networkErrorMessage = Responses::getEnvValue('API_EXCEPTION_ERROR_MSG', '响应异常，服务发生错误');
        $netWorkErrorCode =  Responses::getEnvValue('API_EXCEPTION_ERROR_CODE', 500);


        if(Errors::isExistError()){
            $error = Errors::getError();
        }else{
            $error = new Error(
                $networkErrorMessage,
                $netWorkErrorCode,
                '',
                '',
                '',
                array(
                    '[EXCEPTION_MESSAGE]' =>  $exception->getMessage(),
                    '[EXCEPTION_DATETIME]' =>  date('Y-m-d H:i:s')
                )
            );

            $url = '';
            $method = $request->getCurrentRequest()->getMethod();
            $post_params_content = '';
            $header_content = '';
            $cookies_content = '';
            $ip = '';

            if(!empty($request)){
                $url = $request->getCurrentRequest()->getSchemeAndHttpHost() . $request->getCurrentRequest()->getRequestUri();
                $method = $request->getCurrentRequest()->getMethod();
                $headers = $request->getCurrentRequest()->headers->all();
                $post_params = $request->getCurrentRequest()->request->all();
                $cookies_params =  $request->getCurrentRequest()->cookies->all();
                $ip = $request->getCurrentRequest()->getClientIp();

                foreach ($headers as $key => $value){
                    if(is_array($value)){
                        $value = json_encode($value);
                    }
                    $header_content .= $key . ':' . $value . ';';
                }

                foreach ($post_params as $key => $value){
                    if(is_array($value)){
                        $value = json_encode($value);
                    }
                    $post_params_content .= $key . ':' . $value . ';';
                }

                foreach ($cookies_params as $key => $value){
                    if(is_array($value)){
                        $value = json_encode($value);
                    }
                    $cookies_content .= $key . ':' . $value . ';';
                }
            }

            $userInfo = '[UserAuthId]';

            if(class_exists('\App\Business\AuthBusiness\CurAuthSubject')){
                $userAuth = \App\Business\AuthBusiness\CurAuthSubject::getCurUserAuth();
                if(!empty($userAuth)){
                    $userInfo .= ' ' . $userAuth->getId();
                }
            }

            $time = date('Y-m-d H:i:s');
            $logContent = <<<EOF

[MESSAGE] {$exception->getMessage()}
[FILE] {$exception->getFile()} [[LINE]] {$exception->getLine()} [CODE] {$exception->getCode()}
[Url] {$method}:{$url} [IP] {$ip}
[TRACE]
{$exception->getTraceAsString()}
[Headers] {$header_content}
[Cookies] {$cookies_content}
[Post] {$post_params_content}
{$userInfo}
[Msg] $networkErrorMessage
[Time] {$time}
[END]
\n
EOF;
            //记录日志
            Log::writeLog($logContent);

            //发送报错邮件给开发者
            self::notificationError($logContent);
        }

        switch (SystemBaseController::getReturnType()) {
            case SystemBaseController::RETURN_SHOW_RESOURCE:
                throw new NotFoundHttpException($networkErrorMessage);
                break;
            case SystemBaseController::RETURN_HIDE_RESOURCE:
                return Responses::error($error);
            default:
                return new Response($networkErrorMessage, $netWorkErrorCode);
        }
    }
}