## Building From Source

If you have special requirements that you need to add to OSPOS then you can download the raw code from the github repository to make your changes.  If it's a really cool change that might benefit others then we ask that you consider contributing it to the project.

After you've made your changes you will need to do a "BUILD" on it to add all necessary components that OSPOS needs so that it can actually run.

This documents the "How to Build" processes.  

The goal here was to do a lot of work in setting up and configuring the build process so that the actual build is as simple as possible.  I think we've accomplished that task.

## Requirements

This applies only to the upcoming 3.4.0 release of OSPOS which is being worked on in the CI4 branch.

- Install the latest version of NPM.
- Install the latest version of Composer.

## The Workflow

1. Download the code from the CI4 branch.  (If you are reading this then you probably have already done this and the following step.)
2. Unzip it into a working folder.
3. Start a terminal session frm the root of the working folder.
4. Enter the following commands:
   - npm install
   - npm run build

That's all there is to it.

If you have the database set up and a preconfigured copy of .env just drop then .env file into the root folder then you should be ready to go.

If not then you will need to continue from this point forward with the standard installation instructions, but at this point you have running version of OSPOS.

The .env file should be used to contain your configuration.

If you are running on a Windows based work station (which is what I use) I've added a couple of Powershell scripts to make my life a bit easier, which I share with you.

* build.ps1 - Which runs the build but also rstores the .env from a backup I make of it in a specifically placed folder.
* build-steps.ps1 - This runs through each step of the build and pauses just before it executes the next build step so that the developer can check the results of the previous build step.

## The Result

The build creates a developer version of a running instance of OSPOS.

It is NOT something that should be installed into production.

However, the zip and tar files, found in the root dist folder, are created as part of the process can be used for deploying a production instance of OSPOS.
Good luck with your build.
