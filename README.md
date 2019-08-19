![Art Institute of Chicago](https://raw.githubusercontent.com/Art-Institute-of-Chicago/template/master/aic-logo.gif)


# Assets Data Service
> A slim API in front of our digital asset management system

This microservice is part of our data hub. Check our aggregator for more info:

https://github.com/art-institute-of-chicago/data-aggregator

Prior to February 2019, this microservice provided both assets and collections data. We since transitioned it to providing only assets data. All references to collections data have been removed from this repo. See our collections microservice for more info:

https://github.com/art-institute-of-chicago/data-service-collections

All of the data provided by this API is already available through our institutions's Solr index. This microservice aims to ease the process of retrieving that data. Clients don't need to be familiar with Solr's filter query syntax, nor with our institution's Solr schema. Here, they have a few simple endpoints to retrieve the data they're most likely going to need.

This project was built in-house and is maintained by in-house developers.

It has been deployed in production capacity since mid-2018.


## Features

This project provides the following endpoints:

* `/v1/images` - Get a list of all images, sorted by the date they were
  last updated in descending order. Includes pagination options.
* `/v1/images/X` - Get a single image
* `/v1/sounds` - Get a list of all sounds, in the same manner as `/artworks/`.
* `/v1/sounds/X` - Get a single sound
* `/v1/texts` - Get a list of all texts
* `/v1/texts/X` - Get a single text
* `/v1/videos` - Get a list of all videos
* `/v1/videos/X` - Get a single video

You can check our [API Blueprint](tests/apiary.apib) for more details.


## Overview

This API is part of a larger project at the Art Institute of Chicago to build a data hub for all of our published data--a single point that our forthcoming website and future products can access all the data they might be interested in in a simple, normalized, RESTful way. This project provides an API in front of our collections that will feed into the data hub.


## Requirements

We've run this on our local machines with the following software as minimums:

* Ruby >= 2.3.1
* Node.js >= v0.12.7
* Node Package Manger >= 2.11.3 (comes with node.js)


## Installing

To get started with this project, use the following commands:

```shell
# Clone the repo to your computer
git clone https://github.com/art-institute-of-chicago/data-service-collections.git

# Enter the folder that was created by the clone
cd data-service-collections

# Install all the project's Ruby gems
bundle install

# Install all the project's Node.js packages
npm install
```

Each `install` command uses the languages package managers to install this project's dependencies.


## Configuration

In order for this API to work, you'll need to create a [`config/conf.yaml`](config/conf.yaml) file with a URL to your Solr index. See our [example](config/conf.yaml.example) file for a sample.


## Developing

To run this project on a local server, use the command:

```shell
shotgun
```

This will spin up this project on a local server on port `9393`. You can hit all the endpoints at `localhost:9393/v1`.Shotgun allows you to make changes to the code and see them reflected without needing to restart the server. If this is not a necessity for you, you can also start up the server with the following command:

```shell
rackup
```

This will spin up this project on a local server on port `9292`. You can hit all the endpoints at `localhost:9292/v1`.


## Testing

You can run a test on all this project's endpoints using `dredd`:

```
npm run dredd
```

This will run through our [API Blueprint](tests/apiary.apib) document, construct requests for each documented response, and execute the query to verify that the documented response is actually what is received.


## Contributing

We are not accepting contributions to this repository at this time as it this codebase is nearing its end-of-life. However, this is only one of many components in our [institutional data hub](https://github.com/art-institute-of-chicago?q=data). Check our organization's page for more projects.

This project is released with a Contributor Code of Conduct. By participating in this project you agree to abide by its [terms](CODE_OF_CONDUCT.md).

We welcome bug reports and questions under GitHub's [Issues](issues). For other concerns, you can reach our engineering team at [mailto:engineering@artic.edu](engineering@artic.edu)

## Licensing

This project is licensed under the [GNU Affero General Public License
Version 3](LICENSE).
