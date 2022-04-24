---
date: 2021-11-05
title: 'Moving from GitLab to GitHub'
hero: 'hero-image.jpg'
metaDescription: 'Why I made the jump after years of using GitLab.'
---

Since years I've been a big fan of GitLab. I started there because I could host my own private repos without any costs. At this time, GitHub only allowed private repos for paying customers.

I started using GitHub for my own projects in August 2021 with GitHubs announcement of [Codespaces being available for everyone](https://github.blog/changelog/2021-08-11-codespaces-is-generally-available-for-team-and-enterprise/) with a teams plan.

Just hitting `.` in the browser and having a full-blown VS Code instance at my fingertips had a real wow-effect for me. I really dig GitHubs interface, but if you just want to navigate a project to get a feel for it, I nearly always cloned the repo to my machine so I could use my IDE.  
Not anymore.

For work I could see the use of Codespaces as a development environment on demand, so I started playing around with GitHub a lot more. Spoiler alert: it wasn't Codespaces or the full-blown VS Code instance that kept me their.

Although I knew my way around GitHub's interface, I never used it to maintain a repository. Moving my repos to the same place where I explorer other people's code was something I wanted to do for some time.

So the following thoughts are what drove me to GitHub and what kept me there.

## User interface
GitHub has done so much in the last years to improve their interface. Compared to GitLab it feels light and much more modern.

The addition that you could click through code to get to function definitions or where a variable was declared made open source projects much more accessible in my opinion.

I often browse GitHub on my mobile phone after seeing some interesting tweets. GitHub does a much better job presenting the code and is much faster navigation-wise.

## Importing a project from GitLab
Getting a project into GitHub is relatively simple since the addition of the import dialogue:

{% image 'importing-a-project.png', 'The add dropdown with the import option highlighted' %}

In the following dialogue you need to provide your previous repository's clone URL, add a new name (or just use the one from GitLab), choose of you want to make the repo private or public and you are good to go. 

{% image 'github-import-dialogue.png', 'The GitHub import dialogue' %}

You will be asked to authenticate yourself. These are your GitLab credentials.  
If you are getting the error <span class="bg-red-100 dark:bg-red-800">`No source repositories were detected at https://gitlab.com/timkley/your-repo. Please check the URL and try again.`</span> you maybe copied the wrong URL. In my case though the problem was the activated two-factor-authentication on GitLab. The 2FA flow is not supported when importing to GitHub so you need to turn 2FA off in GitLab to be able to import your repositories.

The import itself is done in the background so you don't have to keep the tab open, you'll get an email after the import is done.  
Depending on the size of your repository this can take a few minutes. My repos are pretty small and the imports never took longer than a minute or two.

## Continuous integration / continuous deployment
I've been using GitLabs shared runners for CI/CD pipelines for a few years now and think I got a good grasp at it (at least for my needs).

I set up automatic testing and deployment with it. And after a few months of iterating I now have a set of in good shape `.gitlab-ci.yml` files I can always reference when starting a new project.

I didn't want to loose this functionality, so with the rising of GitHub Workflows I was curious to see how challenging the setup on GitHub would be.

Spoiler: It wasn't challenging at all.

Maybe my needs aren't that special or hard to solve. But the ease of setting up, for example, automatic testing for a Laravel application really impressed me. Where I tried to get it working for hours on GitLab, keeping countless tutorials and StackOverflow threads open in other tabs, I could find a ready to use [GitHub Action in the Marketplace](https://github.com/marketplace/actions/laravel-tests).

{% image 'green-action.png', 'The test workflow returning green' %}

For deployment I currently use [Deployer](https://deployer.org). Of course there was [an action readily available](https://github.com/marketplace/actions/action-deployer-php) on the GitHub marketplace.

As with GitLab I struggled getting access to my repo. In this case, as last time, I got the following error:

```text
  Host key verification failed.                                                
  fatal: Could not read from remote repository.                                

  Please make sure you have the correct access rights                          
  and the repository exists.  
```

> I don't like the presentation of this error message. The last part about making sure to having the correct access rights is very misleading if you miss the first part.

In my `deploy.php`  I had already set `StrictHostKeyChecking` as SSH option [like described in the docs](https://deployer.org/docs/6.x/hosts) but to no luck.

After logging into the host I wanted to deploy to and running a `git` command my `known_hosts` file was correctly updated and the next automatic deployment with my configured GitHub Action just worked:

{% image 'deployment-works.png', 'Deployment works' %}

To be fair: I learned a lot about CI pipelines in general in the last years, so maybe this comparison isn't that fair.

But it emphasises one main advantage of GitHub: the community and open source approach in general. In the last years I maybe saw one or two open source projects hosted on GitLab. If you see open source code, you probably are surfing GitHub.

People solve their needs and open source the code they write for it in the process, making lifes of people like me a little (or sometimes a lot) easier.

### Downsides
The whole concept of workflows, actions, steps is a little different than pipelines and jobs on GitLab. It's confusing at first to get the semantics right and I'm still searching for a way to trigger a step in a workflow manually after the first steps where successful.

But: it gets easier with every project. After finishing the setup of my first Laravel project the transition of another project went smoothly. Much smoother than my second project on GitLab 😉.

I've gotten much better at adapting to new circumstances lately. In many cases you can achieve the same, or even better, result if you change your point of view. You'll explorer new approaches from which you might benefit. So this may be a little philosophical, but: with every downside, every problem you solve comes an, maybee unseen, upside.

Looking at it from a business point of view (my agency also moved to GitHub), GitHub workflows are sill not as powerful as GitLab. Things like organization wide actions aren't a thing, making it harder to keep your workflows DRY. |[It is planned for Q4 2021](https://github.com/github/roadmap/issues/98), though. Some of our repos still live with GitLab because of this. I don't want to adjust multiple files by hand, so we are patiently waiting.

## Community and open source
I mentioned the community aspect of GitHub a few times before. Having the privilege of browsing others people's code, learning from them and maybe even contribute to open source projects has become more important to me.

It was around 2002 when I wrote my first piece of HTML. It took me this long (yes, that's 19 years 🤯) to gain enough confidence in my skills to publicly build and show things I've done. And I think there is currently no better platform than GitHub for this.

## Final words
My hope is that my move to GitHub helps my motiviation of giving back after years of using open source for my own projects and that, as many others before me, I can provide some value, may it be through an article like this one or contributions to open source in general.

Since I switched to GitHub I published two open source projects! I even got a few stars on one of them 😉. I'm planning to open source some of my other projects, too. For some I had more hopes that they would take of, but since this probably won't happen the most useful thing I could do with them is make them open source.

### My recent open source projects

[An Eleventy plugin for the great Torchlight.dev](https://github.com/timkley/eleventy-plugin-torchlight)

[A PHP SDK for awork.io](https://github.com/timkley/awork-php-sdk)