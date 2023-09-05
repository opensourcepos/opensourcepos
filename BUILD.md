# Building OSPOS

## For Developers and Hobbyists Only

If you are a developer and need to add unique features to OSPOS, you can download the raw code from the github repository and make changes.  If it's a really cool change that might benefit others, we ask that you consider contributing it to the project.

After you've made your changes, you will need to do a "BUILD" on it to add all necessary components that OSPOS needs to be a fully functional application.

This documents the "How to Build" process.  

The goal here was to do a lot of work in setting up and configuring the build process so that the actual build is as simple as possible.  I think we've accomplished that task.

This applies only to the upcoming 3.4.0 release of OSPOS which is being worked on in the CI4 branch, where we are upgrading OSPOS to version 4 of the CodeIgniter framework.

## Prerequisites

- Install the latest version of NPM.
- Install the latest version of Composer.

## The Workflow

1. Download the code from the CI4 branch found at https://github.com/opensourcepos/opensourcepos/tree/ci4-upgrade.
2. Unzip it and copy the contents into the working folder.
3. Start a terminal session from the root of your working folder. For example, I normally open up the working folder in PHPStorm and run the commands from the Terminal provided by the IDE.
4. Enter the following commands:
   - `npm install`
   - `npm run build`

That's all there is to it.  These commands thread a lot of smaller tasks together and run them sequentially.  If you want to run each step manually, then you will need to pay attention to the bouncing between folders that takes place in order to run each task using the correct version of Grunt.

Note: If you receive messages similar to 'codeigniter4/framework v4.3.1 requires ext-intl', this is an indicator that you do not have intl enabled in php.ini

After the build tasks are complete, if you have the database set up and a preconfigured copy of .env, just drop the .env file into the root of the working folder. You should be ready to go.

If you do not have an existing (and upgraded) database, then you will need to continue from this point forward with the standard installation instructions, but at this point you have a runnable version of OSPOS.

### Windows Platform

Using an `.env` file is a convenient approach to store OSPOS configuration.

I've added the following Powershell scripts to make my life a bit easier, which I share with you.

* `build.ps1` - Which runs the build but also restores the .env from a backup I make of it in a specifically placed folder. I place a copy of the configured .env file in a folder that has the following path from the working folder: `../env/<working-folder-name>/.env`


* `build-steps.ps1` - This runs through each step of the build and pauses just before it executes the next build step so that the developer can check the results of the previous build step.

## The Result

The build creates a developer version of a runnable instance of OSPOS.  It contains a ton of developer stuff that **should not be deployed to a production environment**.

Again, the results of this build is NOT something that should be used for production.

However, the zip and tar files, found in the root `dist` folder, are created as part of the build process and can be used for deploying a ***trial production*** instance of OSPOS.

Only official releases should be used for real production.  There is significant risk of failure should you chose to deploy a development branch or even a master branch that the development team hasn't signed off on.

Good luck with your build. Please report any issues you encounter.
