#!groovy

stage 'Build'
node {
    step([$class: 'GitHubSetCommitStatusBuilder'])

    deleteDir()
    checkout scm
//    sh "php /usr/local/bin/composer update -o -n --no-progress --prefer-dist --ignore-platform-reqs"
    stash "project_files"
}

stage 'Acceptance Tests'
userInput = input(message: 'Launch acceptance tests?', parameters: [
    [
        $class: 'TextParameterDefinition',
        name: 'branch',
        defaultValue: '1.4',
        description: 'Branch to build'
    ],
    [
        $class: 'TextParameterDefinition',
        name: 'owner',
        defaultValue: 'akeneo',
        description: 'The repo\'s owner on github'
    ],
    [
        $class: 'ChoiceParameterDefinition',
        name: 'pim_version',
        choices: 'pim-enterprise-dev\npim-community-dev'
    ],
    [
        $class: 'ChoiceParameterDefinition',
        name: 'storage',
        choices: 'odm\norm',
        description: 'Storage used for the build, Mysql Or MongoDb'
    ],
    [
        $class: 'TextParameterDefinition',
        name: 'features',
        defaultValue: 'features,vendor/akeneo/pim-community-dev/features',
        description: 'Features directories to build'
    ],
    [
        $class: 'TextParameterDefinition',
        name: 'ce_branch',
        defaultValue: '1.4.x-dev',
        description: 'Community Edition branch used for the build. If blank, it will use the original composer.json. Leave it empty if you run a community edition build. (Examples : 1.4.x-dev, dev-master, dev-PIM-666)'
    ],
    [
        $class: 'ChoiceParameterDefinition',
        name: 'priority',
        choices: '5\n1\n2\n3\n4\n6\n7\n8\n9',
        description: 'Smaller the better, (And no, that\'s not what she said)'
    ],
    [
        $class: 'ChoiceParameterDefinition',
        name: 'attempts',
        choices: '3\n1\n2\n4\n5'
    ],
    [
        $class: 'TextParameterDefinition',
        name: 'ce_owner',
        defaultValue: 'akeneo',
        description: 'Community Edition owner used for the build'
    ],
    [
        $class: 'ChoiceParameterDefinition',
        name: 'php_version',
        choices: '5.6\n7.0',
        description: 'PHP version to run the tests with'
    ],
    [
        $class: 'ChoiceParameterDefinition',
        name: 'mysql_version',
        choices: '5.5\n5.7',
        description: 'MySQL version to run the tests with'
    ]
])

node {
    sh 'env | sort'
    unstash "project_files"
    sh "/usr/bin/php7.0 /var/lib/distributed-ci/dci-master/bin/build" +
        " -b " + userInput['ce_branch'] +
        " -u " + userInput['ce_owner'] +
        " -p " + userInput['php_version'] +
        " -m " + userInput['mysql_version'] +
        " " + env.WORKSPACE +
        " " + env.BUILD_NUMBER +
        " " + userInput['pim_version'] +
        " " + userInput['storage'] +
        " " + userInput['features'] +
        " " + "pim-community-dev/job/jenkinsfile" +
        " " + userInput['attempts']
}

stage 'Results'
node {
    step([$class: 'ArtifactArchiver', allowEmptyArchive: true, artifacts: 'app/build/screenshots/*.png,app/build/logs/consumer/*.log', defaultExcludes: false, excludes: null])
    step([$class: 'JUnitResultArchiver', testResults: 'app/build/phpunit.xml, app/build/phpspec.xml, app/build/logs/behat/*.xml'])
    step([$class: 'GitHubCommitStatusSetter', resultOnFailure: 'FAILURE', statusMessage: [content: 'Build finished']])
}
