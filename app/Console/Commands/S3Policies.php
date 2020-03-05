<?php

namespace App\Console\Commands;

use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use Illuminate\Console\Command;

use App;
use Aws\Iam\IamClient;
use Aws\Exception\AwsException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class S3Policies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 's3:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create user with access to selected S3 buckets only.';

    protected $credentials;
    protected $region;
    protected $iamClient;
    protected $buckets;
    protected $name;


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->region = config('services.aws.region');
        $this->credentials = new Credentials(config('services.aws.key'), config('services.aws.secret'));

        $this->iamClient = new IamClient([
            'region' => $this->region,
            'version' => 'latest',
            'credentials' => $this->credentials,
        ]);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
//        $this->output->getFormatter()->setStyle('key', new OutputFormatterStyle('orange', null, ['bold']));
//        $this->output->getFormatter()->setStyle('value', new OutputFormatterStyle('red'));
//
//        $key = 'Username';
//        $value = 'noob';
//
//        $this->output->writeln("<key>$key:</key>$value");
//
//        $this->comment("This text is Yellow with Blue background ");
//        dd();
        $this->buckets = $this->selectBucket();
        $name = $this->generateName();

        $group = $this->createGroup($name);
        $policy = $this->createPolicy($name);
        $this->attachGroupPolicy($group, $policy);
        $user = $this->createUser($name);
        $this->addUserToGroup($user, $group);
        $accessKey = $this->createAccessKey($user);

        $this->line('Policy name: ' . $policy['PolicyName']);
        $this->line('Group name: ' . $group['GroupName']);
        $this->line('User name: ' . $user['UserName']);
        $this->line('Access key id: ' . $accessKey['AccessKeyId']);
        $this->line('Secret access key: ' . $accessKey['SecretAccessKey']);
    }

    private function selectBucket()
    {
        $client = new S3Client([
            'region' => $this->region,
            'version' => 'latest',
            'credentials' => $this->credentials,
        ]);

        $result = $client->listBuckets();

        $buckets = array_column($result->get('Buckets'), 'Name');

        return $this->select(
            'Select buckets that you want to give access to:',
            $buckets
        );
    }

    private function generateName()
    {
        $uuid = Str::uuid();
        $name = 'buckets=';
        $name .= implode($this->buckets, ',');
        $name .= '@';
        $name .= $uuid->toString();

        return$name;
    }

    private function createAccessKey(array $user){
        $this->info("Creating access keys.");
        try {
            $result =  $this->iamClient->createAccessKey([
                'UserName' => $user['UserName'],
            ]);

            return $result['AccessKey'];
        } catch (AwsException $e) {
            $this->error($e->getMessage());
            dd($e->getMessage());
        }
    }

    private function createGroup(string $name)
    {
        $this->info("Creating group.");
        try {
            $result =  $this->iamClient->createGroup([
                'GroupName' => $name
            ]);

            return $result['Group'];
        } catch (AwsException $e) {
            $this->error($e->getMessage());
            dd($e->getMessage());
        }
    }
    private function createPolicy($name)
    {
        $this->info("Creating policy.");
        $policyDocument = $this->createPolicyDocument();

        try {
            $result = $this->iamClient->createPolicy([
                'PolicyName' => $name,
                'PolicyDocument' => json_encode($policyDocument, JSON_UNESCAPED_SLASHES)
            ]);

            return $result['Policy'];
        } catch (AwsException $e) {
            $this->error($e->getMessage());
            dd($e->getMessage());
        }
    }

    private function createPolicyDocument()
    {
        $resources = [];

        foreach ($this->buckets as $bucket) {
            array_push($resources, "arn:aws:s3:::$bucket");
            array_push($resources, "arn:aws:s3:::$bucket/*");
        }

        return[
            "Version" => "2012-10-17",
            "Statement" => [
                [
                    "Effect" => "Allow",
                    "Action" => [
                        "s3:GetBucketLocation",
                    ],
                    "Resource" => "arn:aws:s3:::*",
                ],
                [
                    "Effect" => "Allow",
                    "Action" => "s3:*",
                    "Resource" => $resources,
                ],
            ],
        ];
    }

    private function createUser(string $name){
        $this->info("Creating user.");

        try {
            $result = $this->iamClient->createUser([
                'UserName' => $name,
            ]);

            return $result['User'];
        } catch (AwsException $e) {
            $this->error($e->getMessage());
            dd($e->getMessage());
        }
    }

    private function attachGroupPolicy($group, $policy)
    {
        $this->info("Attaching group with policy.");
        try {
            $result = $this->iamClient->attachGroupPolicy([
                'GroupName' => $group['GroupName'],
                'PolicyArn' => $policy['Arn']
            ]);

            return $result;
        } catch (AwsException $e) {
            $this->error($e->getMessage());
            dd($e->getMessage());
        }
    }


    private function addUserToGroup($user, $group)
    {
        $this->info("Adding user to group.");
        try {
            $result = $this->iamClient->addUserToGroup([
                'GroupName' => $group['GroupName'],
                'UserName' => $user['UserName']
            ]);

            return $result;
        } catch (AwsException $e) {
            $this->error($e->getMessage());
            dd($e->getMessage());
        }
    }
}
