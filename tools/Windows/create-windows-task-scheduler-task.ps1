<#
.SYNOPSIS
    Registers a Windows Task Scheduler task that runs OSPOS's background job scheduler every minute.

.DESCRIPTION
    Works around the Task Scheduler GUI's 5-minute floor for repeating triggers by using
    New-ScheduledTaskTrigger's -RepetitionInterval, which allows 1-minute repetition.
    Mirrors the "Run whether user is logged on or not" + "Do not store password" GUI options
    via -LogonType S4U, and leaves "Run with highest privileges" unchecked via -RunLevel Limited.

.PARAMETER PhpPath
    Full path to php.exe.

.PARAMETER ProjectPath
    Full path to the OSPOS project root (the directory containing the `spark` file).

.PARAMETER TaskName
    Name of the scheduled task to create.

.EXAMPLE
    .\create-windows-task-scheduler-task.ps1 -PhpPath 'C:\php\php.exe' -ProjectPath 'C:\laragon\www\opensourcepos'
#>

param(
    [Parameter(Mandatory = $true)]
    [string]$PhpPath,

    [Parameter(Mandatory = $true)]
    [string]$ProjectPath,

    [string]$TaskName = 'OSPOS Task Runner'
)

$Action = New-ScheduledTaskAction -Execute $PhpPath -Argument 'spark tasks:run' -WorkingDirectory $ProjectPath
$Trigger = New-ScheduledTaskTrigger -Once -At (Get-Date) -RepetitionInterval (New-TimeSpan -Minutes 1)
$Principal = New-ScheduledTaskPrincipal -UserId "$env:USERDOMAIN\$env:USERNAME" -LogonType S4U -RunLevel Limited
$Settings = New-ScheduledTaskSettingsSet -StartWhenAvailable

Register-ScheduledTask -TaskName $TaskName -Action $Action -Trigger $Trigger -Principal $Principal -Settings $Settings -Description "Runs OSPOS background tasks every minute"
