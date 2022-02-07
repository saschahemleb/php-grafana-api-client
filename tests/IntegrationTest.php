<?php

declare(strict_types=1);

namespace Saschahemleb\PhpGrafanaApiClient\Tests;

use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\RequestExceptionInterface;
use Saschahemleb\PhpGrafanaApiClient\Authentication;
use Saschahemleb\PhpGrafanaApiClient\Client;
use Saschahemleb\PhpGrafanaApiClient\Resource\Datasource;
use Saschahemleb\PhpGrafanaApiClient\Resource\GenericResponse;
use Saschahemleb\PhpGrafanaApiClient\Resource\Organization;
use Saschahemleb\PhpGrafanaApiClient\Resource\OrganizationUser;
use Saschahemleb\PhpGrafanaApiClient\Resource\OrganizationUserPatch;
use Saschahemleb\PhpGrafanaApiClient\Resource\PagedUsers;
use Saschahemleb\PhpGrafanaApiClient\Resource\Team;
use Saschahemleb\PhpGrafanaApiClient\Resource\User;
use Saschahemleb\PhpGrafanaApiClient\Resource\UserOrganization;
use Saschahemleb\PhpGrafanaApiClient\Resource\UserTeam;
use Spatie\Docker\DockerContainer;
use Spatie\Docker\DockerContainerInstance;

class IntegrationTest extends TestCase
{
    private static DockerContainerInstance $grafana;
    private static Uri $baseUri;

    public static function setUpBeforeClass(): void
    {
        $portOnHost = (int)getenv('GRAFANA_CONTAINER_PORT');
        self::$grafana = DockerContainer::create('grafana/grafana:main')
            ->mapPort((int)getenv('GRAFANA_CONTAINER_PORT'), 3000)
            ->stopOnDestruct()
            ->start();

        static::$baseUri = new Uri("http://localhost:$portOnHost/");

        $client = Client::create(static::$baseUri, Authentication::basicAuth('admin', 'admin'));
        $amountOfTries = 10;
        do {
            try {
                $health = $client->other()->health();
                $exception = null;
            } catch (\Throwable $exception) {
                usleep((int)(0.3 * 1_000_000));
            }
            $amountOfTries--;
        } while ($amountOfTries && $exception !== null);
    }

    public function testAuthentication()
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );

        $user = $client->user()->getSingleUserById(1);
        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('admin', $user->getLogin());
    }

    public function testAuthenticationFailure()
    {
        $credentials = ['unknown', 'nobody'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );

        $this->expectException(\RuntimeException::class);
        $client->user()->getSingleUserById(1);
    }

    public function testUserSearchUsers()
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );

        $users = $client->user()->searchUsers(20, 1);
        $this->assertIsArray($users);
        $this->assertInstanceOf(User::class, $users[0]);
    }

    public function testUserSearchUsersWithPaging()
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );

        $users = $client->user()->searchUsersWithPaging(20, 1);
        $this->assertInstanceOf(PagedUsers::class, $users);
        $this->assertIsInt($users->getTotalCount());
        $this->assertSame(20, $users->getPerPage());
        $this->assertSame(1, $users->getPage());
        $this->assertInstanceOf(User::class, $users[0]);
    }

    public function testUserGetSingleUserById()
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );

        $user = $client->user()->getSingleUserById(1);
        $this->assertInstanceOf(User::class, $user);
    }

    public function testUserGetSingleUserByLoginOrEmail()
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );

        $userByLogin = $client->user()->getSingleUserByLoginOrEmail('admin');
        $userByEmail = $client->user()->getSingleUserByLoginOrEmail('admin@localhost');

        $this->assertInstanceOf(User::class, $userByLogin);
        $this->assertSame('admin', $userByLogin->getLogin());
        $this->assertSame('admin@localhost', $userByLogin->getEmail());
        $this->assertInstanceOf(User::class, $userByEmail);
        $this->assertSame('admin', $userByEmail->getLogin());
        $this->assertSame('admin@localhost', $userByEmail->getEmail());
    }

    public function testUserUserUpdate()
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );
        $user = $client->user()->getSingleUserById(1);
        $newTheme = $user->getTheme() === 'light' ? 'dark' : 'light';
        $user->setTheme($newTheme);

        $client->user()->updateUser($user);

        $user = $client->user()->getSingleUserById(1);
        $this->assertSame($newTheme, $user->getTheme());
    }

    public function testUserGetOrganizationsForUser()
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );

        $orgs = $client->user()->getOrganizationsForUser(1);

        $this->assertIsArray($orgs);
        $this->assertInstanceOf(UserOrganization::class, $orgs[0]);
        $this->assertSame('Main Org.', $orgs[0]->getName());
        $this->assertSame('Admin', $orgs[0]->getRole());
    }

    public function testTeamAddTeam()
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );
        $team = Team::create('MyTestTeam', 1)->setEmail('email@test.com');

        $newTeam = $client->team()->addTeam($team);
        $this->assertInstanceOf(Team::class, $newTeam);
        $this->assertEquals(1, $newTeam->getId());
    }

    /**
     * @depends testTeamAddTeam
     */
    public function testTeamGetTeamById()
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );

        $team = $client->team()->getTeamById(1);
        $this->assertInstanceOf(Team::class, $team);
        $this->assertEquals(1, $team->getId());
    }

    /**
     * @depends testTeamAddTeam
     */
    public function testTeamAddTeamMember()
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );

        $client->team()->addTeamMember(1, 1);

        $team = $client->team()->getTeamById(1);
        $this->assertInstanceOf(Team::class, $team);
        $this->assertGreaterThan(0, $team->getId());
    }

    /**
     * @depends testTeamAddTeamMember
     */
    public function testUserGetTeamsForUser()
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );

        $teams = $client->user()->getTeamsForUser(1);

        $this->assertIsArray($teams);
        $this->assertInstanceOf(UserTeam::class, $teams[0]);
        $this->assertSame(1, $teams[0]->getOrgId());
        $this->assertSame('MyTestTeam', $teams[0]->getName());
    }

    public function testUserActualUser()
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );

        $user = $client->user()->actualUser();

        $this->assertInstanceOf(User::class, $user);
    }

    public function testAdminCreateNewUser()
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );

        $user = $client->admin()->createNewUser(
            User::create('newuser@example.com', 'New User', 'newuser'),
            'password'
        );

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals(2, $user->getId());

        return $user;
    }

    public function testAdminDeleteUser()
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );
        $user = $client->admin()->createNewUser(
            User::create(__METHOD__.'@localhost.de', __METHOD__, __METHOD__),
            __METHOD__
        );

        $client->admin()->deleteUser($user->getId());

        $this->expectException(RequestExceptionInterface::class);
        $client->user()->getSingleUserById($user->getId());
    }

    public function testOrganizationGetCurrentOrganization()
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );

        $org = $client->organization()->getCurrentOrganization();

        $this->assertInstanceOf(Organization::class, $org);
        $this->assertSame(1, $org->getId());
        $this->assertSame('Main Org.', $org->getName());
    }

    public function testOrganizationGetAllUsersWithinTheCurrentOrganization()
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );

        $users = $client->organization()->getAllUsersWithinTheCurrentOrganization();

        $this->assertIsArray($users);
        $this->assertInstanceOf(OrganizationUser::class, $users[0]);
    }

    public function testOrganizationCreateOrganization()
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );
        $organization = Organization::create('MyTestOrg');

        $newOrganization = $client->organization()->createOrganization($organization);

        $this->assertInstanceOf(Organization::class, $newOrganization);
        $this->assertIsInt($newOrganization->getId());
        $this->assertSame('MyTestOrg', $newOrganization->getName());

        return $newOrganization;
    }

    /**
     * @depends testAdminCreateNewUser
     * @depends testOrganizationCreateOrganization
     */
    public function testOrganizationAddUserInOrganization(User $user, Organization $organization)
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );

        $client->organization()->addUserInOrganization(
            $organization->getId(),
            'Viewer',
            $user->getLogin(),
        );

        $this->assertCount(2, $client->organization()->getUsersInOrganization($organization->getId()));
    }

    /**
     * @depends testAdminCreateNewUser
     * @depends testOrganizationCreateOrganization
     * @depends testOrganizationAddUserInOrganization
     */
    public function testOrganizationUpdateUserInOrganization(User $user, Organization $organization)
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );

        $client->organization()->updateUserInOrganization(
            $organization->getId(),
            'Admin',
            $user->getId(),
        );

        $organizationUsers = $client->organization()->getUsersInOrganization($organization->getId());
        $this->assertCount(2, $organizationUsers);
        $this->assertEquals($user->getId(), $organizationUsers[1]->getUserId());
        $this->assertEquals('Admin', $organizationUsers[1]->getRole());
    }

    /**
     * @depends testOrganizationCreateOrganization
     */
    public function testOrganizationUpdateOrganization(Organization $organization)
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );
        $organization->setName(__METHOD__);

        $client->organization()->updateOrganization($organization);
        $this->assertEquals(__METHOD__, $client->organization()->getOrganizationById($organization->getId())->getName());
    }

    public function testOrganizationDeleteOrganization()
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );
        $organization = $client->organization()->createOrganization(Organization::create(__METHOD__));

        $client->organization()->deleteOrganization($organization->getId());

        $this->expectException(RequestExceptionInterface::class);
        $client->organization()->getOrganizationById($organization->getId());
    }

    public function testOrganizationDeleteUserInOrganization()
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );
        $organization = $client->organization()->createOrganization(Organization::create(__METHOD__));
        $user = $client->admin()->createNewUser(User::create(__METHOD__.'@localhost.de', __METHOD__, __METHOD__), __METHOD__);
        $client->organization()->addUserInOrganization($organization->getId(), 'Editor', __METHOD__);

        $client->organization()->deleteUserInOrganization($organization->getId(), $user->getId());

        $remainingUsersInOrganization = $client->organization()->getUsersInOrganization($organization->getId());
        $this->assertCount(1, $remainingUsersInOrganization);
    }

    /**
     * @depends testOrganizationCreateOrganization
     */
    public function testDatasourceCreateDatasource(Organization $organization)
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );

        $datasource = $client->inOrganization($organization->getId(), function (Client $client) {
            return $client->datasource()->createDatasource(
                Datasource::create(
                    'testDatasource',
                    'prometheus',
                    'http://prometheus.local',
                )
                    ->setJsonData([
                        'httpHeaderName1' => 'X-Scope-Orgid',
                        'httpMethod' => 'POST',
                    ])
                    ->setSecureJsonData(['httpHeaderValue1' => 'my-orgid'])
            );
        });

        $this->assertInstanceOf(Datasource::class, $datasource);
        $this->assertIsInt($datasource->getId());
        $this->assertEquals($organization->getId(), $datasource->getOrganizationId());
        $this->assertEquals('testDatasource', $datasource->getName());
        $this->assertEquals('prometheus', $datasource->getType());
        $this->assertEquals('http://prometheus.local', $datasource->getUrl());
        $this->assertEquals(['httpHeaderName1' => 'X-Scope-Orgid', 'httpMethod' => 'POST'], $datasource->getJsonData());
    }

    /**
     * @depends testOrganizationCreateOrganization
     */
    public function testDatasourceCreateDatasourceWithoutJsonData(Organization $organization)
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );

        $datasource = $client->inOrganization($organization->getId(), function (Client $client) {
            return $client->datasource()->createDatasource(
                Datasource::create(
                    'testDatasourceWithoutJsonData',
                    'prometheus',
                    'http://prometheus.local',
                )
            );
        });

        $this->assertInstanceOf(Datasource::class, $datasource);
        $this->assertIsInt($datasource->getId());
        $this->assertEquals($organization->getId(), $datasource->getOrganizationId());
        $this->assertEquals('testDatasourceWithoutJsonData', $datasource->getName());
        $this->assertEquals('prometheus', $datasource->getType());
        $this->assertEquals('http://prometheus.local', $datasource->getUrl());
        $this->assertEmpty($datasource->getJsonData());
    }

    public function testDatasourceGetAllDatasources()
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );
        $organization = $client->organization()->createOrganization(Organization::create('testDatasourceGetAllDatasources'));
        $client->inOrganization($organization->getId(), function (Client $client) {
            $client->datasource()->createDatasource(
                Datasource::create(
                    'testDatasourceGetAllDatasources1',
                    'prometheus',
                    'http://prometheus.local',
                ));
            $client->datasource()->createDatasource(
                Datasource::create(
                    'testDatasourceGetAllDatasources2',
                    'prometheus',
                    'http://prometheus.local',
                )
                    ->setJsonData([
                        'httpHeaderName1' => 'X-Scope-Orgid',
                        'httpMethod' => 'POST',
                    ])
                    ->setSecureJsonData(['httpHeaderValue1' => 'my-orgid'])
            );
        });

        $datasources = $client->inOrganization($organization->getId(), fn() => $client->datasource()->getAllDatasources());

        $this->assertCount(2, $datasources);
        $firstDatasource = $datasources[0];
        $this->assertInstanceOf(Datasource::class, $firstDatasource);
    }

    public function testDatasourceGetDatasourceById()
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );
        $organization = $client->organization()->createOrganization(Organization::create('testDatasourceGetDatasourceById'));
        $datasourceId = null;
        $client->inOrganization($organization->getId(), function (Client $client) use (&$datasourceId) {
            $datasource = $client->datasource()->createDatasource(
                Datasource::create(
                    'testDatasourceGetDatasourceById',
                    'prometheus',
                    'http://prometheus.local',
                ));
            $datasourceId = $datasource->getId();
        });

        $datasource = $client->inOrganization($organization->getId(), fn() => $client->datasource()->getDatasourceById($datasourceId));

        $this->assertInstanceOf(Datasource::class, $datasource);
        $this->assertEquals('testDatasourceGetDatasourceById', $datasource->getName());
    }

    public function testDatasourceGetDatasourceByUid()
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );
        $organization = $client->organization()->createOrganization(Organization::create('testDatasourceGetDatasourceByUid'));
        $datasourceUid = 'kLtEtcRGk';
        $client->inOrganization($organization->getId(), function (Client $client) use ($datasourceUid) {
            $datasource = $client->datasource()->createDatasource(
                Datasource::create(
                    'testDatasourceGetDatasourceById',
                    'prometheus',
                    'http://prometheus.local',
                )->setUid($datasourceUid)
            );
        });

        $datasource = $client->inOrganization($organization->getId(), fn() => $client->datasource()->getDatasourceByUid($datasourceUid));

        $this->assertInstanceOf(Datasource::class, $datasource);
        $this->assertEquals('testDatasourceGetDatasourceById', $datasource->getName());
    }

    public function testDatasourceGetDatasourceByName()
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );
        $organization = $client->organization()->createOrganization(Organization::create('testDatasourceGetDatasourceByName'));
        $name = 'testDatasourceGetDatasourceByName';
        $client->inOrganization($organization->getId(), function (Client $client) use ($name) {
            $datasource = $client->datasource()->createDatasource(
                Datasource::create(
                    $name,
                    'prometheus',
                    'http://prometheus.local',
                )
            );
        });

        $datasource = $client->inOrganization($organization->getId(), fn() => $client->datasource()->getDatasourceByName($name));

        $this->assertInstanceOf(Datasource::class, $datasource);
        $this->assertEquals($name, $datasource->getName());
    }

    public function testDatasourceUpdateDatasource()
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );
        $organization = $client->organization()->createOrganization(Organization::create('testDatasourceUpdateDatasource'));
        /** @var Datasource $datasource */
        $datasource = $client->inOrganization($organization->getId(), function (Client $client) {
            return $client->datasource()->createDatasource(
                Datasource::create(
                    'testDatasourceUpdateDatasource1',
                    'prometheus',
                    'http://prometheus.local',
                )
            );
        });

        $datasource->setName('testDatasourceUpdateDatasource2');
        $updated = $client->inOrganization($organization->getId(), fn() => $client->datasource()->updateDatasource($datasource));

        $this->assertInstanceOf(Datasource::class, $updated);
        $this->assertEquals('testDatasourceUpdateDatasource2', $updated->getName());
    }

    public function testDatasourceDeleteDatasourceById()
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );
        $organization = $client->organization()->createOrganization(Organization::create('testDatasourceDeleteDatasourceById'));
        $datasourceId = null;
        $client->inOrganization($organization->getId(), function (Client $client) use (&$datasourceId) {
            $datasource = $client->datasource()->createDatasource(
                Datasource::create(
                    'testDatasourceDeleteDatasourceById',
                    'prometheus',
                    'http://prometheus.local',
                ));
            $datasourceId = $datasource->getId();
        });

        $client->inOrganization($organization->getId(), fn() => $client->datasource()->deleteDatasourceById($datasourceId));

        $this->assertCount(0, $client->inOrganization($organization->getId(), fn() => $client->datasource()->getAllDatasources()));
    }

    public function testDatasourceDeleteDatasourceByUid()
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );
        $organization = $client->organization()->createOrganization(Organization::create('testDatasourceDeleteDatasourceByUid'));
        $datasourceUid = 'sOasW2AxD';
        $client->inOrganization($organization->getId(), function (Client $client) use ($datasourceUid) {
            $datasource = $client->datasource()->createDatasource(
                Datasource::create(
                    'testDatasourceDeleteDatasourceByUid',
                    'prometheus',
                    'http://prometheus.local',
                )->setUid($datasourceUid)
            );
        });

        $client->inOrganization($organization->getId(), fn() => $client->datasource()->deleteDatasourceByUid($datasourceUid));

        $this->assertCount(0, $client->inOrganization($organization->getId(), fn() => $client->datasource()->getAllDatasources()));
    }

    public function testDatasourceDeleteDatasourceByName()
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );
        $organization = $client->organization()->createOrganization(Organization::create('testDatasourceDeleteDatasourceByName'));
        $datasourceName = 'testDatasourceDeleteDatasourceByName';
        $client->inOrganization($organization->getId(), function (Client $client) use ($datasourceName) {
            $datasource = $client->datasource()->createDatasource(
                Datasource::create(
                    $datasourceName,
                    'prometheus',
                    'http://prometheus.local',
                )
            );
        });

        $client->inOrganization($organization->getId(), fn() => $client->datasource()->deleteDatasourceByName($datasourceName));

        $this->assertCount(0, $client->inOrganization($organization->getId(), fn() => $client->datasource()->getAllDatasources()));
    }

    /**
     * @depends testOrganizationCreateOrganization
     */
    public function testInOrganization(Organization $organization)
    {
        $credentials = ['admin', 'admin'];
        $client = Client::create(
            static::$baseUri,
            Authentication::basicAuth(...$credentials)
        );

        $testOrganization = $client->inOrganization($organization->getId(), fn() => $client->organization()->getCurrentOrganization());
        $mainOrgOrganization = $client->organization()->getCurrentOrganization();

        $this->assertInstanceOf(Organization::class, $mainOrgOrganization);
        $this->assertInstanceOf(Organization::class, $testOrganization);
        $this->assertEquals(1, $mainOrgOrganization->getId());
        $this->assertEquals($organization->getId(), $testOrganization->getId());
    }
}
