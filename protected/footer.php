<?php
echo <<<END
			<br class="clearad">
			</div>			
			<div class="foot">
				<div class="clickable_span footopt" onclick="return controller('email', 'email.cfrm')">Contact</div>
				<a class="link footopt" href="http://collabor8r.com/terms/"> Terms of Service </a>
				<a class="link footopt" href="http://collabor8r.com/about/"> About Us</a>
			</div>
		</div>
		<div class="pmask" id="pmask">
		</div>
		<div class="pup" id="pup">
				<span class="clickable_span" onclick="popDown()">
					<div id="pupx" class="pnav">Close X</div>
				</span>
				<div id="pupcontents">
					Loading...
				</div>
		</div>
	</body>
</html>
END;
?>