( function () {
	'use strict';

	var config = window.sbcCalculatorData || {};
	var i18n = config.i18n || {};

	function getCategory( bmi ) {
		if ( bmi < 18.5 ) {
			return i18n.underweight || 'Underweight';
		}

		if ( bmi < 25 ) {
			return i18n.normal || 'Normal weight';
		}

		if ( bmi < 30 ) {
			return i18n.overweight || 'Overweight';
		}

		return i18n.obesity || 'Obesity';
	}

	function parseNumber( value ) {
		var parsed = parseFloat( value );

		return Number.isFinite( parsed ) ? parsed : null;
	}

	function getCurrentUnit( calculator ) {
		return calculator.getAttribute( 'data-current-unit' ) || calculator.getAttribute( 'data-default-unit' ) || 'metric';
	}

	function clearFeedback( calculator ) {
		setError( calculator, '' );
		setResult( calculator, '-', '-' );
	}

	function setActiveUnit( calculator, unit ) {
		var metricFields = calculator.querySelector( '.sbc-fields--metric' );
		var imperialFields = calculator.querySelector( '.sbc-fields--imperial' );
		var toggles = calculator.querySelectorAll( '.sbc-unit-toggle__button' );
		var unitTag = calculator.querySelector( '.sbc-calculator__unit-tag' );

		calculator.setAttribute( 'data-current-unit', unit );
		calculator.classList.toggle( 'sbc-unit-metric', unit === 'metric' );
		calculator.classList.toggle( 'sbc-unit-imperial', unit === 'imperial' );

		if ( metricFields && imperialFields ) {
			metricFields.hidden = unit !== 'metric';
			imperialFields.hidden = unit !== 'imperial';
			metricFields.setAttribute( 'aria-hidden', unit === 'metric' ? 'false' : 'true' );
			imperialFields.setAttribute( 'aria-hidden', unit === 'imperial' ? 'false' : 'true' );
			toggleGroupInputs( metricFields, unit === 'metric' );
			toggleGroupInputs( imperialFields, unit === 'imperial' );
		}

		if ( unitTag ) {
			unitTag.textContent = unit === 'imperial'
				? ( i18n.imperialUnits || 'Imperial units' )
				: ( i18n.metricUnits || 'Metric units' );
		}

		toggles.forEach( function ( toggle ) {
			var isActive = toggle.getAttribute( 'data-unit' ) === unit;

			toggle.classList.toggle( 'sbc-unit-toggle__button--active', isActive );
			toggle.setAttribute( 'aria-pressed', isActive ? 'true' : 'false' );
			toggle.tabIndex = isActive ? 0 : -1;
		} );
	}

	function toggleGroupInputs( group, isActive ) {
		group.querySelectorAll( 'input' ).forEach( function ( input ) {
			input.disabled = ! isActive;
		} );
	}

	function setError( calculator, message ) {
		var errorNode = calculator.querySelector( '.sbc-error' );

		if ( ! errorNode ) {
			return;
		}

		errorNode.textContent = message;
		errorNode.hidden = ! message;
	}

	function setResult( calculator, bmi, category ) {
		var resultNumber = calculator.querySelector( '.sbc-result__number' );
		var resultCategory = calculator.querySelector( '.sbc-result__category-text' );

		if ( resultNumber ) {
			resultNumber.textContent = bmi;
		}

		if ( resultCategory ) {
			resultCategory.textContent = category;
		}
	}

	function calculateMetric( calculator ) {
		var height = parseNumber( calculator.querySelector( '[id$="-height-cm"]' ).value );
		var weight = parseNumber( calculator.querySelector( '[id$="-weight-kg"]' ).value );

		if ( ! height || ! weight || height <= 0 || weight <= 0 ) {
			return null;
		}

		var heightMeters = height / 100;

		return weight / ( heightMeters * heightMeters );
	}

	function calculateImperial( calculator ) {
		var feet = parseNumber( calculator.querySelector( '[id$="-height-ft"]' ).value );
		var inches = parseNumber( calculator.querySelector( '[id$="-height-in"]' ).value );
		var weight = parseNumber( calculator.querySelector( '[id$="-weight-lb"]' ).value );

		if ( feet === null ) {
			feet = 0;
		}

		if ( inches === null ) {
			inches = 0;
		}

		if ( weight === null || weight <= 0 ) {
			return null;
		}

		var totalInches = ( feet * 12 ) + inches;

		if ( totalInches <= 0 ) {
			return null;
		}

		return ( weight / ( totalInches * totalInches ) ) * 703;
	}

	function bindCalculator( calculator ) {
		var button = calculator.querySelector( '.sbc-button' );
		var toggles = calculator.querySelectorAll( '.sbc-unit-toggle__button' );
		var defaultUnit = getCurrentUnit( calculator );

		setActiveUnit( calculator, defaultUnit );

		toggles.forEach( function ( toggle ) {
			toggle.addEventListener( 'click', function () {
				setActiveUnit( calculator, toggle.getAttribute( 'data-unit' ) );
				clearFeedback( calculator );
			} );

			toggle.addEventListener( 'keydown', function ( event ) {
				if ( event.key !== 'ArrowLeft' && event.key !== 'ArrowRight' ) {
					return;
				}

				event.preventDefault();

				var direction = event.key === 'ArrowRight' ? 1 : -1;
				var toggleList = Array.prototype.slice.call( toggles );
				var currentIndex = toggleList.indexOf( toggle );
				var nextIndex = ( currentIndex + direction + toggleList.length ) % toggleList.length;
				var nextToggle = toggleList[ nextIndex ];

				if ( nextToggle ) {
					nextToggle.focus();
					nextToggle.click();
				}
			} );
		} );

		if ( ! button ) {
			return;
		}

		button.addEventListener( 'click', function () {
			var unit = getCurrentUnit( calculator );
			var bmi = unit === 'imperial' ? calculateImperial( calculator ) : calculateMetric( calculator );

			if ( bmi === null ) {
				setError( calculator, i18n.errorMessage || 'Please enter valid height and weight values.' );
				setResult( calculator, '-', '-' );
				return;
			}

			setError( calculator, '' );
			setResult( calculator, bmi.toFixed( 1 ), getCategory( bmi ) );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		document.querySelectorAll( '.sbc-calculator' ).forEach( bindCalculator );
	} );
}() );
