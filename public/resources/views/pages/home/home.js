'use strict';

angular.module('myApp.home', ['ngRoute'])
    .controller('HomeCtrl', ['$scope', function($scope) {
        $scope.doInit = function() {
            $('#slider').slick({
                dots: true,
                infinite: true,
                speed: 500,
                fade: true,
                cssEase: 'linear',
                arrows: false,
                autoplay: true
            });

            $('.masthead-section').addClass('animate');

            $('#slider').on('setPosition', function(event, slick) {
                $('#slider').find('.masthead-section').addClass('animate');
            });

            $('#slider').on('afterChange', function(event, slick, currentSlide) {
                var current = $('#slider .slide').get(currentSlide);
                $(current).find('.masthead-section').removeClass('animate');
            });

            $('#search_types > li > a').click(function() {
                $('#search_types > li').removeClass('selected');
                $(this).parent().addClass('selected');
                if($(this).parent().attr('id') === 'search_location') {
                    $('#search_card').removeClass('advanced-search');
                } else {
                    $('#search_card').addClass('advanced-search');
                }
                $('#search_type_selector > div').show();
                $('#search_type_selector > div > a').removeClass('selected');
                return false;
            });

            $('#search_types > li > a').mouseenter(function() {
                $('#search_types').addClass('on-hover');
            });

            $('#search_types > li > a').mouseleave(function() {
                $('#search_types').removeClass('on-hover');
            });

            $('#search_type_selected').click(function() {
                var sel = this;
                $('#search_type_selector > div > a').each(function(i, obj) {
                    if($(obj).text() == $(sel).text()) {
                        $(obj).addClass('selected');
                    } else {
                        $(obj).removeClass('selected');
                    }
                });
                $('#search_type_selector > div').slideToggle();
                return false;
            });

            $('#search_type_selector > div > a').click(function() {
                $('#search_type_selector > div > a').removeClass('selected');
                $(this).addClass('selected');
                $('#search_type_selected').text($(this).text());
                $('#search_type_selector > div').slideUp();
                return false;
            });
        };

        angular.element(document).ready(function () {
            $scope.doInit();
        });
    }]);