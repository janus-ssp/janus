# Contributing code
The best way of contributing to JANUS is to create a [pull request](https://github.com/janus-ssp/janus/pulls) and attach the code. Please give time for other users to comment on the supplied patch. So do not expect inclusion of patches strait away.

## Coding standard
JANUS uses the PSR2 coding standard for all new files (in the ``src`` dir) older code (in the ``lib`` dir) was built using the PEAR coding standard.

# How to submit code

When submitting code it is important that you only submit actual code changes and that your code changes only addresses a single issue.

This means that if you have fixed several issues, then you should split the code changes into multiple submits, each solving a single issue.

Do not submit code that changes the indentation or add new lines on code that are not directly affected by the actual code changes. This pollutes the code diff and make it really hard to see the actual code change. If the indentation do not adhere to the coding standard for JANUS, then you should make a separate code submit only containing the new indentations along with an appropriate message.

When submitting code, you have to distinguish between bugfixes and new features. This have a consequence on how you should contribute your code.

## Bugfixing

Bugfixes should be committed to the latest release brach first and the merged to trunk, if the bug is still present in the trunk version as well.

## New features

New features should always be committed to trunk and newer to any branch

## In an issue

If you do not have access to commit code directly, you should always start by creating an issue. State what is broken or the proposed new feature and attach the code change that will fix the issue. A member of the team will then review your patch and apply it.

Attached patches should always be submitted as git diffs applied on the root directory of JANUS and remember to make the patch on the latest version of JANUS.

## Committing code

It is recommended that you create an issue on both bugfixes and new features, even if you have commit rights to the JANUS project. Issues for small bugfixes can be omitted, but is not encouraged. Issues should always be made for new features. This enables others to comment on the idea and hopefully make the solutions better.

### Commit messages

It is important to give short and precise commit messages. It makes it easier to get an overview over many commits and it makes it easier to make change logs when doing new releases.

It is preferred that commit messages are used to update issues instead of manually adding comments on the issues. The complete documentation on how to do this can be found [here](http://code.google.com/p/support/wiki/IssueTracker#Integration_with_version_control).

The following examples shows how to mark an issues as fixed via the commit message

    Fixes issue ABC
    Add support for new feature.

Putting the commit message on the above form will make the issue as fixed and add an appropriate message on the issue itself.

If a commit only partially fixes an issue, you can update an issue in the following way

    Update issue ABC
    Added the basics of the new feature
