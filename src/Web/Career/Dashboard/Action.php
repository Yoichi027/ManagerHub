<?php
declare(strict_types=1);
namespace App\Web\Career\Dashboard;
use App\Domain\Career\CareerRepository;
use App\Domain\Catalog\ClubRepository;
use App\Domain\Season\SeasonRepository;
use App\Web\Identity\RequireAuthenticatedUser;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\User\CurrentUser;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
final readonly class Action {
 public function __construct(private RequireAuthenticatedUser $auth, private CurrentUser $currentUser, private CurrentRoute $route, private CareerRepository $careers, private SeasonRepository $seasons, private ClubRepository $clubs, private WebViewRenderer $view, private ResponseFactoryInterface $responses, private UrlGeneratorInterface $urls) {}
 public function __invoke(): ResponseInterface { $redirect=$this->auth->redirectIfGuest(); if($redirect!==null)return $redirect; try{$id=Uuid::fromString((string)$this->route->getArgument('id'));}catch(\Throwable){return $this->redirect();} $career=$this->careers->findById($id); if($career===null||!$career->userId->equals(Uuid::fromString((string)$this->currentUser->getId())))return $this->redirect(); $season=$this->seasons->findActiveByCareerId($career->id); if($season===null)return $this->redirect(); $club=$season->managedClub->id===null?null:$this->clubs->findById($season->managedClub->id); return $this->view->render(__DIR__.'/template',['career'=>$career,'season'=>$season,'clubLogoUrl'=>$club?->logoUrl]); }
 private function redirect(): ResponseInterface { return $this->responses->createResponse(303)->withHeader('Location',$this->urls->generate('dashboard')); }
}
