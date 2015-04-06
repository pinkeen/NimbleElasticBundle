NimbleElasticBundle
===================

A simple bundle that integrates _elasticsearch_ into your _Symfony_ project using the official
[elasticsearch PHP client](https://github.com/elastic/elasticsearch-php).

## Why another _elasticsearch_ bundle?

Most ES bundles I found assume that one entity is one ES document. This is very limiting, as very often
denormalization is needed with more complex data. Your domain model does not need to be reflected in your
elasticsearch mapping.

Also the most popular bundle does not give you an easy way to update the "master" document when any of your
child entities change. This should be simple with this bundle.

This bundle directly exposes the official _elasticsearch_ `Client` without wrapping it too much. I don't see
much reason (besides rudimentary error checking) for using any query builders when most queries can be built
dynamically as array and serialized to JSON directly.

This bundle provides only basic functionalities, which means you will have to write more code, but gives you
flexiblity in return. For example:
* A mechanism for synchronizing your entities with ES is provided (doctrine listeners or you have to dispatch
create/update/delete events manually).
* A mechanism for transforming your entities into ES documents is provided but you have to write the actual 
transformer yourself. This is better for performance and a must if you want flexibility. It also allows you 
completetly decouple the ES from your model (no unnecessary getters needed on your entities, yay!).
* No mechnanism for automagically transforming ES results back into your entities will be ever provided 
(probably), because this would seriously limit the flexibility. Usually you could implement this yourself in 
a few lines, with your performance considerations in mind.

It will have great docs. ;)

## Installation

The usual [Symfony stuff](http://symfony.com/doc/current/cookbook/bundles/installation.html).

The **composer.json** needs: `"nimble/elastic-bundle": "dev-master@dev",`.

The **AppKernel.php** needs: `new Nimble\ElasticBundle\NimbleElasticBundle(),`.
