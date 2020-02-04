<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use App\Events\AbstractNotificationEmail;
use App\Events\NotificationNewEmail;
use App\Events\NotificationOldEmail;
use App\Events\NotificationInviteEmail;
use App\Events\NotificationRestoreLogin;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NotificationInviteEmailSubscriber implements EventSubscriberInterface
{
    /** @var string */
    const H1_STYLE = 'style="font-size:34px; font-weight:300; margin: 10px 0"';

    /** @var string */
    const P_SECONDARY_STYLE = 'style="font-size: 16px; line-height: 19px; margin:0 0 0px 0; padding: 0px; color:#0a0a0a; font-weight:300"';

    /** @var string */
    const A_STYLE = 'style="color: #2199e8;"';

    /** @var string */
    const DESTINATION_DOMAIN = 'https://gromada.dmsu.gov.ua';

    /** @var string */
    const HELP_URL = '/help';

    /** @var \Swift_Mailer */
    private $mailer;

    /** @var  string */
    private $sender;

    /** @var string */
    private $domain;

    /**
     * ConfirmUserEmailSubscriber constructor.
     * @param \Swift_Mailer $mailer
     * @param string $sender
     * @param string $domain
     */
    public function __construct(\Swift_Mailer $mailer, string $sender, string $domain)
    {
        $this->mailer = $mailer;
        $this->sender = $sender;
        $this->domain = $domain;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            NotificationInviteEmail::class => 'onSendEmail',
            NotificationNewEmail::class => 'onSendEmail',
            NotificationOldEmail::class => 'onSendEmail',
            NotificationRestoreLogin::class => 'onSendEmail'
        ];
    }

    /**
     * @param AbstractNotificationEmail $event
     */
    public function onSendEmail(AbstractNotificationEmail $event): void
    {
        $subject = $event->getSubject();

        $body = $this->getBodyTop() . $this->getBodyMiddle($event) . $this->getBodyBottom($event);

        $message = (new \Swift_Message())
            ->setSubject($subject)
            ->setTo($event->getTo())
            ->setFrom($this->sender)
            ->setBody($body, 'text/html');

        $this->mailer->send($message);
    }

    /**
     * @param AbstractNotificationEmail $event
     * @return string
     */
    protected function getBodyMiddle(AbstractNotificationEmail $event): string
    {
        $href = $this->domain . $event->getUrlPath();
        if ($event->addLink === true) {
            $body = '<h1 ' . self::H1_STYLE . '>' . $event->getBodyH1() .
                '!</h1><p ' . self::P_SECONDARY_STYLE . '> ' . $event->getBodyP() . '<a ' . self::A_STYLE . ' href="' . $href . '">' . $href . '</a></p>';
        } else {
            $body = '<h1 ' . self::H1_STYLE . '>' . $event->getBodyH1() .
                '!</h1><p ' . self::P_SECONDARY_STYLE . '> ' . $event->getBodyP() . '<a></a></p>';
        }

        return $body;
    }

    /**
     * @return string
     */
    protected function getBodyTop()
    {
        return
            '<' . '!' . 'DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd"><html><body>
            <table style="margin:0 auto; width: 580px;">
                <tbody>
                    <tr>
                        <td valign="top" align="left">
                            <center>
                                <table>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <table>
                                                    <tbody>
                                                        <tr>
                                                            <th>
                                                                <table style="font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; font-size: 16px; text-align: left">
                                                                    <tbody>
                                                                        <tr>
                                                                            <th>
                                                                                <br>
                                                                                    <center>
                                                                                        <img width="150" align="middle" height="150" src="' . $this->domain . '/images/login-sprite.png">
                                                                                    </center>

                                                                                <br>';
    }

    /**
     * @param AbstractNotificationEmail $event
     * @return string
     */
    protected function getBodyBottom(AbstractNotificationEmail $event)
    {
        return
            '<br> <p ' . self::P_SECONDARY_STYLE . '>' . $event->getFooterPart1() . '
                      <a ' . self::A_STYLE . ' target="_blank" href="' . self::DESTINATION_DOMAIN . self::HELP_URL . '">' . $event->getFooterPart2() . '</a>' . $event->getFooterPart3() . '
                        <br>
                       <a ' . self::A_STYLE . ' target="_blank" value="+380444814931" href="tel:+380444814931">+38 (044) 481-49-31</a>
                          <br>
                        <a ' . self::A_STYLE . ' target="_blank" value="+380503126606" href="tel:+380503126606">+38 (050) 312-66-06</a>
                          <br>
                        <a ' . self::A_STYLE . ' target="_blank" value="+380675499450" href="tel:+380675499450">+38 (067) 549-94-50</a>
                          </p>
                                  </th>
                                    </tr>
                            </tbody>
                            </table>
                            </th>
                            </tr>
                            </tbody>
                            </table>
                            </td>
                            </tr>
                        </tbody>
                    </table>
                </center>
                </td>
                </tr>
            </tbody>
            </table></body></html>';
    }
}
