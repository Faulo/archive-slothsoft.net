<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	
	<xsl:template match="/data">
		<html ng-app="Amber">
		  <head>
			<script src="/getScript.php/lib/angular"></script>
			<script><![CDATA[
angular.module('Amber', []);
/*
angular.element(
	document
).ready(
	function() {
		angular.bootstrap(
			document
		);
    }
);
*/
			]]></script>
		  </head>
		  <body>
			<div>
			  <label>Name:</label>
			  <input type="text" ng-model="yourName" placeholder="Enter a name here"/>
			  <hr/>
			  <h1>Hello {{yourName}}!</h1>
			</div>
		  </body>
		</html>
	</xsl:template>
	
	<!--
	<xsl:template match="/data">
		<html ng-app="app">
		  <head>
			<script src="/getScript.php/lib/angular"></script>
			<script src="/getScript.php/lib/angular.components"></script>
			<script src="/getScript.php/lib/angular.app"></script>
		  </head>
		  <body>
			<tabs>
			  <pane title="Localization">
				<span>Date: {{ '2012-04-01' | date:'fullDate' }}</span><br/>
				<span>Currency: {{ 123456 | currency }}</span><br/>
				<span>Number: {{ 98765.4321 | number }}</span><br/>
			  </pane>
			  <pane title="Pluralization">
				<div ng-controller="BeerCounter">
				  <div ng-repeat="beerCount in beers">
					<ng-pluralize count="beerCount" when="beerForms"></ng-pluralize>
				  </div>
				</div>
			  </pane>
			</tabs>
		  </body>
		</html>
	</xsl:template>
	-->
</xsl:stylesheet>