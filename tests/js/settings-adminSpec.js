/**
* ownCloud
*
* @author Vincent Petry
* @copyright 2014 Vincent Petry <pvince81@owncloud.com>
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the License, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/

describe('OCA.NMC_Welcome_Popup.Admin tests', function() {
	var Admin = OCA.NMC_Welcome_Popup.Admin;
	beforeEach(function() {
		$('#testArea').append(
			'<ul class="slide-list settings-hint">' +
				'<li data-id="1">' +
					'<a href="#">Slide 1</a>' +
				'</li>' +
				'<li data-id="2" class="">' +
					'<a href="#">Slide 2</a>' +
				'</li>' +
				'<li class="add-slide" style=""><a href="#" class="button"><span class="icon-add"></span></a></li>' +
			'</ul>' +
			'<div>' +
			'<form class="uploadButton" method="post" action="/index.php/apps/nmc_welcome_popup/ajax/uploadImage" data-image-key="image">' +
				'<input type="hidden" id="slide-image" data-imgurl="" value="welcome_image_1" />' +
				'<input type="hidden" name="key" id="image-name" data-key="welcome_image" value="welcome_image_1" />' +
				'<label for="uploadimage"><span style="min-width: 50px;">Image</span></label>' +
				'<input id="uploadimage" class="fileupload" name="image" type="file" />' +
				'<div class="image-label">' +
					'<label for="uploadimage" class="button icon-upload svg" id="uploadimage" title="Header image for the pop-up"></label>' +
					'<label id="image-uploaded"></label>' +
					'<a id="remove-img" class="icon-delete" style="display: none;"></a>' +
				'</div>' +
			'</form>' +
		'</div>'
		);
	});
	describe('initialization', function() {
		it('initializes first slide', function() {
			expect(Admin.currentConfig).toEqual('1');
			expect(Admin.slideIds).toEqual('1');
			expect(Admin.noSlides).toEqual('0');
			expect(Admin.maxSlides).toEqual('5');
			expect(Admin._getAppConfig).toBeDefined();
			expect(Admin.init).toBeDefined();
			expect(Admin.addSlide).toBeDefined();
			expect(Admin.removeSlide).toBeDefined();
			OCA.NMC_Welcome_Popup.Admin.init(function() {
				$('.slide-list li[data-id="' + OCA.NMC_Welcome_Popup.Admin.currentConfig + '"]').addClass('active');
			});
			expect(fakeServer.requests.length).toEqual(1);
			request = fakeServer.requests[0];
			expect(request.method).toEqual('GET');
			// expect(request.url).toEqual(OC.getRootPath() + '/ocs\/v2.php\/apps\/provisioning_api\/api\/v1\/config\/apps\/nmc_welcome_popup\/slideIds?format=json');
			expect(request.url).toEqual(OC.linkToOCS('apps/provisioning_api/api/v1', 2) + 'config/apps/nmc_welcome_popup/slideIds?format=json');
			var data = {
				"ocs": {
					"meta": {
						"status": "ok",
						"statuscode": 200,
						"message": "OK"
					},
					"data": {
						"data": "1,2"
					}
				}
			};
			request.respond(
				200,
				{ 'Content-Type': 'application/json' },
				JSON.stringify(data)
				);
			// fakeServer.respondWith(/\/ocs\/v2.php\/apps\/provisioning_api\/api\/v1\/config\/apps\/nmc_welcome_popup\/slideIds?format=json/, [
			// 	200, {
			// 		"Content-Type": "application/json"
			// 	},
			// 	JSON.stringify(data)
			// ]);
			expect($('.slide-list li[data-id="' + Admin.currentConfig + '"]').hasClass('active')).toEqual(true);
		});
	});

	describe('AddSlide', function() {
		it('add a new slide', function() {
			var addSlideBtn = $('.slide-list .add-slide');
			addSlideBtn.click();
			Admin.addSlide(function (nextId) {
				var slideIds = Admin.slideIds.split(',');
				$('<li data-id="' + nextId + '"><a>Slide ' + slideIds.length + '</a></li>').insertBefore(addSlideBtn[0]);
				// switchSlide(nextId);
				if (slideIds.length >= Admin.maxSlides) {
					addSlideBtn.hide();
				}
			});
			// expect(Admin.maxSlides).toEqual('5');
			// expect(Admin._getAppConfig).toBeDefined();
			// expect(Admin.addSlide).toBeDefined();
			// expect(Admin.removeSlide).toBeDefined();
			// OCA.NMC_Welcome_Popup.Admin.init(function() {
			// 	$('.slide-list li[data-id="' + OCA.NMC_Welcome_Popup.Admin.currentConfig + '"]').addClass('active');
			// });
			expect(fakeServer.requests.length).toEqual(1);
			request = fakeServer.requests[0];
			expect(request.method).toEqual('POST');
			expect(request.url).toEqual(
				OC.linkToOCS('apps/provisioning_api/api/v1', 2) + 'config/apps/nmc_welcome_popup/slideIds'
			);
			var expectedQuery = {
				value: '1,2,3'
			};
			var query = OC.parseQueryString(request.requestBody);
			expect(query).toEqual(expectedQuery);

			// fakeServer.requests[0].respond(
			// 	200,
			// 	{ 'Content-Type': 'application/xml' },
			// 	JSON.stringify(ocsResponse)
			// );
			// expect(fakeServer.requests.length).toEqual(1);
			// request = fakeServer.requests[0];
			// expect(request.url).toEqual(OC.getRootPath() + '/ocs\/v2.php\/apps\/provisioning_api\/api\/v1\/config\/apps\/nmc_welcome_popup\/slideIds?format=json');
			// expect(request.url).toEqual(OC.linkToOCS('apps/provisioning_api/api/v1', 2) + 'config/apps/nmc_welcome_popup/slideIds?format=json');
			var data = '<?xml version="1.0"?>' +
			'<ocs>' +
				'<meta>' +
					'<status>ok</status>'+
					'<statuscode>200</statuscode>' +
					'<message>OK</message>' +
				'</meta>' +
				'<data/>' +
			'</ocs>';
			request.respond(
				200,
				{ 'Content-Type': 'application/xml' },
				data
				);
			// fakeServer.respondWith(/\/ocs\/v2.php\/apps\/provisioning_api\/api\/v1\/config\/apps\/nmc_welcome_popup\/slideIds?format=json/, [
			// 	200, {
			// 		"Content-Type": "application/json"
			// 	},
			// 	JSON.stringify(data)
			// ]);
			expect(Admin.slideIds).toEqual('1,2,3');
			expect(Admin.noSlides).toEqual('0');
			// expect($('.slide-list li[data-id="' + Admin.currentConfig + '"]').hasClass('active')).toEqual(true);
		});
	});

	describe('RemoveSlide', function() {
		it('remove a slide', function(done) {
			var addSlideBtn = $('.slide-list .add-slide');
			var slideDataId = '.slide-list li[data-id=';
			addSlideBtn.click();
			Admin.removeSlide(function(currentConfig) {
				// OC.msg.finishedSuccess('#remove_slide_msg', "Slide removed");
				var slideIds = Admin.slideIds.split(',');
				if (Admin.noSlides === '0') {
					$(slideDataId + '"' + currentConfig + '"]').remove();
					// renameSlides(slideIds);
					// switchSlide(slideIds[0]);
				} else {
					$(slideDataId + '"' + currentConfig + '"]').attr('data-id', slideIds[0]);
					// switchSlide(slideIds[0]);
				}
				if (addSlideBtn.is(":hidden")) {
					addSlideBtn.show();
				}
			});
			expect(fakeServer.requests.length).toEqual(1);
			request = fakeServer.requests[0];
			expect(request.method).toEqual('DELETE');
			expect(request.url).toEqual(OC.getRootPath() + '/index.php/apps/nmc_welcome_popup/ajax/slideSettings/' + Admin.currentConfig);
			// request.respond(200);
			var data = {
				"data": {
					"message": "Deleted"
				},
				"status": "success"
			};
			request.respond(
				200,
				{ 'Content-Type': 'application/json' },
				JSON.stringify(data)
			);

			expect(fakeServer.requests.length).toEqual(2);
			request = fakeServer.requests[1];
			expect(request.method).toEqual('POST');
			expect(request.url).toEqual(
				OC.linkToOCS('apps/provisioning_api/api/v1', 2) + 'config/apps/nmc_welcome_popup/slideIds'
			);
			var expectedQuery = {
				value: '2,3'
			};
			var query = OC.parseQueryString(request.requestBody);
			expect(query).toEqual(expectedQuery);
			var data = '<?xml version="1.0"?>' +
			'<ocs>' +
				'<meta>' +
					'<status>ok</status>'+
					'<statuscode>200</statuscode>' +
					'<message>OK</message>' +
				'</meta>' +
				'<data/>' +
			'</ocs>';
			request.respond(
				200,
				{ 'Content-Type': 'application/xml' },
				data
				);
			expect(Admin.slideIds).toEqual('2,3');
			expect(Admin.noSlides).toEqual('0');
			// expect($('.slide-list li[data-id="' + Admin.currentConfig + '"]').hasClass('active')).toEqual(true);
		});
	});

});