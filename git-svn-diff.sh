#!/bin/bash
#
# git-svn-diff originally by (http://mojodna.net/2009/02/24/my-work-git-workflow.html)
# modified by mike@mikepearce.net
# modified by aconway@[redacted] - handle diffs that introduce new files
# modified by t.broyer@ltgt.net - fixes diffs that introduce new files
# modified by m@rkj.me - fix sed syntax issue in OS X
# modified by rage-shadowman - cleaned up finding of SVN info and handling of path parameters
# modified by tianyapiaozi - cleaned up some diff context lines
#
# Generate an SVN-compatible diff against the tip of the tracking branch
 
# Usage: git-svn-diff.sh FROM TO
# or: git-svn-diff.sh TO
# or: git-svn-diff.sh
#
# Gets the SVN diff from the latest dcommitted version of FROM to the latest version of TO
 
usage_exit ()
{
echo
echo "Gets the SVN compatible diff from the latest dcommitted version of FROM to the latest version of TO"
echo
echo "Usage: $0 FROM TO"
echo " or: $0 TO"
echo " or: $0"
echo
echo "If FROM is not supplied we will use the latest dcommitted version of HEAD"
echo
echo "If TO is not supplied we will use the latest (possibly non-committed) version of HEAD"
echo
exit 1;
}
 
FROM=${2:+$1}
TO=${2:-$1}
 
# make sure FROM and TO exist or were not specified
if ! git show-branch $FROM >/dev/null 2>&1
then
usage_exit
fi
 
if ! git show-branch $TO >/dev/null 2>&1
then
usage_exit
fi
 
LATEST_DCOMMIT_HASH=`git log $FROM --grep=^git-svn-id: --first-parent -1 --pretty=format:'%H'`
SVN_REV=`git svn find-rev $LATEST_DCOMMIT_HASH`
 
# do the diff and masssage into SVN format
git diff --no-prefix $LATEST_DCOMMIT_HASH $TO |
sed -e "/--- \/dev\/null/{ N; s|^--- /dev/null\n+++ \(.*\)|--- \1 (revision 0)\n+++ \1 (working copy)|;}" \
-e "s/^--- .*/& (revision $SVN_REV)/" \
-e "s/^+++ .*/& (working copy)/" \
-e "s/^\(@@.*@@\).*/\1/" \
-e "s/^diff --git [^[:space:]]*/Index:/" \
-e "s/^index.*/===================================================================/" \
-e "/^new file mode [0-9]\+$/d"
