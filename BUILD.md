# Building OSPOS

## For Developers and Hobbyists Only

If you are a developer and need to add unique features to OSPOS, you can download the raw code from the github repository and make changes.  If it's a really cool change that might benefit others, we ask that you consider contributing it to the project.

After you've made your changes, you will need to do a "BUILD" on it to add all necessary components that OSPOS needs to be a fully functional application.

This documents the "How to Build" process.

The goal here is to set up and configure the build process so that the actual build is as simple as possible.

The build process uses the build tools "npm" and "gulp" to piece everything together.

This applies only to the upcoming 3.4.0 release of OSPOS which is being worked on in the CI4 branch, where we are upgrading OSPOS to version 4 of the CodeIgniter framework.

## Prerequisites

- Install the latest version of NPM (tested using version 9.4.2)
- Install the latest version of Composer (tested using composer 2.5.1)

## The Workflow

1. Download the code from the CI4 branch found at https://github.com/opensourcepos/opensourcepos/tree/ci4-upgrade.
2. Unzip it and copy the contents into the working folder.
3. Start a terminal session from the root of your working folder. For example, I normally open up the working folder in PHPStorm and run the commands from the Terminal provided by the IDE.
4. Enter the following three commands in sequence:
	- `composer install`
	- `npm install`
	- `npm run build`

That's all there is to it.

Note: If you receive messages similar to 'codeigniter4/framework v4.3.1 requires ext-intl', this is an indicator that you do not have intl enabled in php.ini

After the build tasks are complete, if you have the database set up and a preconfigured copy of .env, just drop the .env file into the root of the working folder. You should be ready to go.

If you do not have an existing (and upgraded) database, then you will need to continue from this point forward with the standard installation instructions, but at this point you have a runnable version of OSPOS.

### Windows Platform

Using an `.env` file is a convenient approach to store OSPOS configuration.

I've added the following Powershell scripts to make my life a bit easier, which I share with you.

* `build.ps1` - Which runs the build but also restores the .env from a backup I make of it in a specifically placed folder. I place a copy of the configured .env file in a folder that has the following path from the working folder: `../env/<working-folder-name>/.env`

### Containerized setup
Development using docker has the advantage that all the application's dependencies are contained within the docker environment. During development we want to have a live version of the code in the container when we edit it. This is accomplished by mounting the application folder within the /app of the docker container. 

The file permissions for the repository in the container should be the same as on the host. That's why we have to startthe PHP process in docker with the host current uid. 

```
export USERID=$(id -u)
export GROUPID=$(id -g)
docker-compose -f docker-compose.dev.yml up
```

## The Result

The build creates a developer version of a runnable instance of OSPOS.  It contains a ton of developer stuff that **should not be deployed to a production environment**.

Again, the results of this build is NOT something that should be used for production.

However, the zip and tar files, found in the root `dist` folder, are created as part of the build process and can be used for deploying a ***trial production*** instance of OSPOS.

Only official releases should be used for real production.  There is significant risk of failure should you chose to deploy a development branch or even a master branch that the development team hasn't signed off on.

Good luck with your build. Please report any issues you encounter.
