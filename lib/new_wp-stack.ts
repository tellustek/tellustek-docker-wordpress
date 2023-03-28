import * as path from 'path'
import * as cdk from 'aws-cdk-lib'
import { Construct } from 'constructs'
import * as ec2 from 'aws-cdk-lib/aws-ec2'
import * as ecs from 'aws-cdk-lib/aws-ecs'
import * as efs from 'aws-cdk-lib/aws-efs'
import * as acm from 'aws-cdk-lib/aws-certificatemanager'
import * as ecsPatterns from 'aws-cdk-lib/aws-ecs-patterns'
import * as ecrAssets from 'aws-cdk-lib/aws-ecr-assets'
import * as elbv2 from 'aws-cdk-lib/aws-elasticloadbalancingv2'
import * as dotenv from 'dotenv'
dotenv.config()

export class NewWpStack extends cdk.Stack {

  constructor(scope: Construct, id: string, props?: cdk.StackProps) {
    super(scope, id, props);

    const vpc = new ec2.Vpc(this, 'VPC', {
      natGateways: 0,
      subnetConfiguration: [{
        name: 'Public',
        subnetType: ec2.SubnetType.PUBLIC
      }]
    })

    const securityGroup = new ec2.SecurityGroup(this, 'SG', {
      vpc,
      allowAllOutbound: true
    })
    securityGroup.addIngressRule(ec2.Peer.anyIpv4(), ec2.Port.tcp(80), 'Allow all HTTP')
    securityGroup.addIngressRule(ec2.Peer.anyIpv4(), ec2.Port.tcp(443), 'Allow all HTTPS')

    const cluster = new ecs.Cluster(this, 'Cluster', {
      vpc,
      enableFargateCapacityProviders: true,
    })

    const volumeName = 'efs'
    const fileSystem = new efs.FileSystem(this, 'EFS', {
      vpc,
      vpcSubnets: {
        subnetType: ec2.SubnetType.PUBLIC
      },
      encrypted: false,
      lifecyclePolicy: efs.LifecyclePolicy.AFTER_14_DAYS,
      // removalPolicy: cdk.RemovalPolicy.DESTROY
    })

    const taskDefinition = new ecs.FargateTaskDefinition(this, 'Task', {
      memoryLimitMiB: 1024,
      cpu: 512,
      volumes: [{
        name: volumeName,
        efsVolumeConfiguration: {
          fileSystemId: fileSystem.fileSystemId
        }
      }]
    })

    const dockekImageAsset = new ecrAssets.DockerImageAsset(this, 'WordpressImage', {
      directory: path.join(__dirname, '../')
    })
    const container = taskDefinition.addContainer('WPContainer', {
      // image: ecs.ContainerImage.fromRegistry('amazon/amazon-ecs-sample'),
      // image: ecs.ContainerImage.fromRegistry('wordpress'),
      image: ecs.ContainerImage.fromDockerImageAsset(dockekImageAsset),
      environment: {
        WORDPRESS_DB_NAME: process.env.PRODUCTION_DB_NAME as string,
        WORDPRESS_DB_HOST: process.env.PRODUCTION_DB_HOST as string,
        WORDPRESS_DB_USER: process.env.PRODUCTION_DB_USER as string,
        WORDPRESS_DB_PASSWORD: process.env.PRODUCTION_DB_PASSWORD as string,
      },
      secrets: {},
      portMappings: [{ containerPort: 80 }],
    })
    taskDefinition.defaultContainer?.addMountPoints({
      containerPath: '/var/www/html',
      readOnly: false,
      sourceVolume: volumeName
    })

    const service = new ecs.FargateService(this, 'Service', {
      cluster,
      taskDefinition,
      desiredCount: 1,
      assignPublicIp: true,
    })

    // Attach your service to an existing Application Load Balancer
    const lb = new elbv2.ApplicationLoadBalancer(this, 'ALB', {
      vpc,
      securityGroup,
      internetFacing: true
    })

    const httpListener = lb.addListener('HttpListener', {
      port: 80,
    })
    const httpTargetGroup = httpListener.addTargets('HttpTargetGroup', {
      targets: [service],
      port: 80,
    })


    const certificate = new acm.Certificate(this, 'Cert', {
      domainName: '*.chronocats.com',
      validation: acm.CertificateValidation.fromDns()
    })
    const httpsListener = lb.addListener('HttpsListener', {
      certificates: [certificate],
      port: 443,
    })
    const httpsTargetGroup = httpsListener.addTargets('HttpsTargetGroup', {
      targets: [service],
      port: 80,
    })

    // httpsTargetGroup.healthCheck = {
    //   path: "/wp-includes/images/blank.gif",
    //   interval: cdk.Duration.minutes(1),
    // }

    fileSystem.connections.allowDefaultPortFrom(service.connections)

    new cdk.CfnOutput(this, 'LBUrl', { value: lb.loadBalancerDnsName })
  }
}
