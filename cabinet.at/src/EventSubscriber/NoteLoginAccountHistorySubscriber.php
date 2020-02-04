<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use App\Events\NoteLoginAccountHistory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class NoteLoginAccountHistorySubscriber implements EventSubscriberInterface
{
    /** @var RequestStack */
    private $requestStack;

    /** @var EntityManagerInterface */
    private $em;

    /**
     * NoteLoginAccountHistorySubscriber constructor.
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $em
     */
    public function __construct(RequestStack $requestStack, EntityManagerInterface $em)
    {
        $this->requestStack = $requestStack;
        $this->em = $em;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            NoteLoginAccountHistory::class => 'onNoteLoginAccountHistory'
        ];
    }

    /**
     * @param NoteLoginAccountHistory $event
     * @throws \Exception
     */
    public function onNoteLoginAccountHistory(NoteLoginAccountHistory $event): void
    {
        $user = $event->getUser();

        $user->setLastloginDate(new \DateTimeImmutable('now'));
        $user->setLastloginIp($this->requestStack->getCurrentRequest()->getClientIp());

        $this->em->flush();;
    }
}