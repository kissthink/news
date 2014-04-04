###

ownCloud - News

@_author Bernhard Posselt
@copyright 2012 Bernhard Posselt dev@bernhard-posselt.com

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
License as published by the Free Software Foundation; either
version 3 of the License, or any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU AFFERO GENERAL PUBLIC LICENSE for more details.

You should have received a copy of the GNU Affero General Public
License along with this library.  If not, see <http://www.gnu.org/licenses/>.

###


angular.module('News').controller 'ItemController',
['$scope', '$sce', 'ItemBusinessLayer', 'FeedModel', 'FeedLoading', 'FeedBusinessLayer',
'Language', 'AutoPageLoading', 'Compact',
($scope, $sce, ItemBusinessLayer, FeedModel, FeedLoading, FeedBusinessLayer,
Language, AutoPageLoading, Compact) ->

	class ItemController

		constructor: (@_$scope, @_$sce, @_itemBusinessLayer, @_feedModel,
		              @_feedLoading, @_autoPageLoading, @_feedBusinessLayer,
		              @_language, @_compact) ->
			@_autoPaging = true

			@_$scope.to_trusted = (html_code) =>
				return @_$sce.trustAsHtml(html_code)

			@_$scope.itemBusinessLayer = @_itemBusinessLayer
			@_$scope.feedBusinessLayer = @_feedBusinessLayer

			@_$scope.edit = (feedId) =>
				feed = @_feedModel.getById(feedId)
				feed.editing = true
				feed.originalValue = feed.title

			@_$scope.cancel = (feedId) =>
				feed = @_feedModel.getById(feedId)
				feed.editing = false
				feed.title = feed.originalValue

			@_$scope.isLoading = =>
				return @_feedLoading.isLoading()

			@_$scope.isAutoPaging = =>
				return @_autoPageLoading.isLoading()

			@_$scope.getFeedTitle = (feedId) =>
				feed = @_feedModel.getById(feedId)
				if angular.isDefined(feed)
					return feed.title
				else
					return ''


			@_$scope.getRelativeDate = (date) =>
				if date
					return @_language.getMomentFromTimestamp(date).fromNow()
				else
					return ''

			@_$scope.loadNew = =>
				@_$scope.refresh = true
				@_itemBusinessLayer.loadNew =>
					@_$scope.refresh = false


			@_$scope.$on 'readItem', (scope, data) =>
				@_itemBusinessLayer.setRead(data)

			@_$scope.$on 'autoPage', =>
				if @_autoPaging
					# prevent multiple autopaging requests
					@_autoPaging = false
					@_itemBusinessLayer.loadNext (data) =>
						@_autoPaging = true


			@_$scope.isCompactView = =>
				return @_compact.isCompact()


	return new ItemController($scope, $sce, ItemBusinessLayer, FeedModel, FeedLoading,
	                          AutoPageLoading, FeedBusinessLayer, Language,
	                          Compact)
]
