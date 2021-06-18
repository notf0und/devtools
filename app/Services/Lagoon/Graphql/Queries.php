<?php
namespace App\Services\Lagoon\Graphql;

class Queries
{
    const ALL_PROJECTS= <<<'GRAPHQL'
        query allProjects {
            allProjects {
                id
                name
                gitUrl
                availability
                branches
                autoIdle
            }
        }
    GRAPHQL;

    const PROJECT_BY_NAME = <<<'GRAPHQL'
        query projectByName($name: String!) {
            projectByName(name: $name) {
                id
                name
                environments {
                    id
                    name
                    openshiftProjectName
                }
            }
        }
    GRAPHQL;
}
