importScripts('pouchdb.min.js');
importScripts('pouchdb.find.js');
try
{
	var customer_limit = 100;
	var item_limit = 100;

	var one_day_in_minutes = 24*60;//init value 24 hours

	var ajax = function(url, data, callback, type) {
	  var data_array, data_string, idx, req, value;
	  if (data == null) {
	    data = {};
	  }
	  if (callback == null) {
	    callback = function() {};
	  }
	  if (type == null) {
	    //default to a GET request
	    type = 'GET';
	  }
	  data_array = [];
	  for (idx in data) {
	    value = data[idx];
	    data_array.push("" + idx + "=" + value);
	  }
	  data_string = data_array.join("&");
	  req = new XMLHttpRequest();
	  req.open(type, url, false);
	  req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  req.onreadystatechange = function() {
	    if (req.readyState === 4 && req.status === 200) {
	      return callback(req.responseText);
	    }
	  };
	  req.send(data_string);
	  return req;
	};

	settings = {};


	//TODO need to check health (bad phppos_settings) NO tables
	var db_settings = new PouchDB('phppos_settings',{revs_limit: 1});
	var db_customers = new PouchDB('phppos_customers',{revs_limit: 1});
	var db_items = new PouchDB('phppos_items',{revs_limit: 1});

	self.addEventListener("message", function(e) 
	{
		settings = e.data;
		if(settings.offline_mode_sync_period)
		one_day_in_minutes = settings.offline_mode_sync_period * 60;
	
		db_settings.get('customers_sync_last_run_time',async function (error, doc) 
		{
			if (error) 
			{
				await db_settings.put({'_id':'customers_sync_last_run_time','value': 0 });
				loadCustomersOffline();
			} 
			else 
			{
				var last_run = doc.value;
				var time_since_last_run_in_minutes = Math.floor((Math.abs(Date.now() - last_run)/1000)/60);
			
				if (time_since_last_run_in_minutes >=one_day_in_minutes)
				{
					loadCustomersOffline();
				}
			}
		});	
	
	
		db_settings.get('items_sync_last_run_time',async function (error, doc) 
		{
			if (error) 
			{
				await db_settings.put({'_id':'items_sync_last_run_time','value': 0 });
				loadItemsOffline();
			} 
			else 
			{
				var last_run = doc.value;
				var time_since_last_run_in_minutes = Math.floor((Math.abs(Date.now() - last_run)/1000)/60);
			
				if (time_since_last_run_in_minutes >=one_day_in_minutes)
				{
					loadItemsOffline();
				}
			}
		});	
	
	}, false);


	async function loadCustomersOffline(base_url)
	{
		try
		{
			await db_customers.createIndex({
			  index: {
			    fields: ['first_name']
			  }
			});

			await db_customers.createIndex({
			  index: {
			    fields: ['last_name']
			  }
			});
	
			await db_customers.createIndex({
			  index: {
			    fields: ['first_name', 'last_name']
			  }
			});
	
			await db_customers.createIndex({
			  index: {
			    fields: ['full_name']
			  }
			});
	
	
			await db_customers.createIndex({
			  index: {
			    fields: ['account_number']
			  }
			});
		}
		catch (err)
		{
			//If we cannot make indexes we are in a bad state and we need to start over
			postMessage('delete_all_client_side_dbs');
			throw new Error('Invalid state resetting databases');
		}
	
		try 
		{
			await db_customers.get('_design/search');
		}
		catch (err) //Need to make the doc
		{
		    var ddoc = {
		      _id: '_design/search',
		      views: {
		       search: {
		        map: function(doc) {
		        const regex = /[\s\.;]+/gi;
		        ['last_name','first_name','full_name','account_number'].forEach(field => {
		          if (doc[field]) {
				  
					  emit(doc[field].toLocaleLowerCase(), [field, doc[field]]);
				  
					  const words = doc[field].replaceAll(regex,
		              ',').split(',');
		            words.forEach(word => {
		              word = word.trim();
		              if (word.length) {
		                emit(word.toLocaleLowerCase(), [field, word]);
		              }
		            });
		          }
		        });
		       }.toString()
		      }
		      }
		    };
			try
			{
				await db_customers.put(ddoc);
			}
			catch(err2)
			{
				//If we cannot make indexes we are in a bad state and we need to start over
				postMessage('delete_all_client_side_dbs');
				throw new Error('Invalid state resetting databases');
			}
		}

	
		db_settings.get('offline_customer_offset',async function (error, doc) 
		{
			if (error) 
			{
				customer_offset = 0;
				try
				{
					await db_settings.put({'_id':'offline_customer_offset','value': customer_offset });
				}
				catch(error2)
				{
					//If we cannot make indexes we are in a bad state and we need to start over
					postMessage('delete_all_client_side_dbs');
					throw new Error('Invalid state resetting databases');
				}
				var url = settings.site_url+'/sales/customers_offline_data/'+customer_limit+"/"+customer_offset;
				ajax(url, {}, processCustomerAjax, 'POST');
			
			} 
			else 
			{
				var new_offline_customer_offset = {'_id': 'offline_customer_offset','value': (parseInt(doc.value))};
				new_offline_customer_offset['_rev'] = doc._rev;
				try
				{
					await db_settings.put(new_offline_customer_offset,{force: true});
				}
				catch(error2)
				{
					//If we cannot make indexes we are in a bad state and we need to start over
					postMessage('delete_all_client_side_dbs');
					throw new Error('Invalid state resetting databases');
				}
				var url = settings.site_url+'/sales/customers_offline_data/'+customer_limit+"/"+(parseInt(doc.value));
				ajax(url, {}, processCustomerAjax, 'POST');
			
			}
		});		
	}

	async function processCustomerAjax(data) 
	{
		var customers = JSON.parse(data);

		for(var k=0;k<customers.length;k++)
		{
			var customer = customers[k];
			var new_customer = {'_id': customer.person_id+'_customer',first_name: customer.first_name,last_name:customer.last_name,full_name:customer.first_name+' '+customer.last_name,account_number:customer.account_number,person_id:customer.person_id};
			try
			{
				var doc = await db_customers.get(customers[k].person_id+"_customer");
				new_customer['_rev'] = doc._rev;
				await db_customers.put(new_customer,{force: true});
			}
			catch(error)
			{
				await db_customers.put(new_customer);
			}	
		}
	
	    await db_customers.query('search', {
	      reduce: true
	    });


		db_settings.get('offline_customer_offset',async function (error, doc) 
		{
			//Keep going
			if (customers.length)
			{
				var new_offline_customer_offset = {'_id': 'offline_customer_offset','value': parseInt(doc.value)+customer_limit};
				new_offline_customer_offset['_rev'] = doc._rev;
				await db_settings.put(new_offline_customer_offset,{force: true});
			
				var url = settings.site_url+'/sales/customers_offline_data/'+customer_limit+"/"+(parseInt(doc.value)+customer_limit);
				ajax(url, {}, processCustomerAjax, 'POST');
			}
			else
			{
				var new_offline_customer_offset = {'_id': 'offline_customer_offset','value': 0};
				new_offline_customer_offset['_rev'] = doc._rev;
				await db_settings.put(new_offline_customer_offset,{force: true});
			
				db_settings.get('customers_sync_last_run_time',async function (error2, doc2) 
				{	
					//Put the Date in we just ran so it don't run for a bit
					var new_customers_sync_last_run_time = {'_id': 'customers_sync_last_run_time','value': Date.now()};
					new_customers_sync_last_run_time['_rev'] = doc2._rev;
					await db_settings.put(new_customers_sync_last_run_time,{force: true});
				});	
			}
		
		});	
	

	}
	
	async function loadItemsOffline(base_url)
	{
		try
		{
			await db_items.createIndex({
			  index: {
			    fields: ['name']
			  }
			});

			await db_items.createIndex({
			  index: {
			    fields: ['item_number']
			  }
			});
	
			 await db_items.createIndex({
			  index: {
			    fields: ['product_id']
			  }
			});
		}
		catch(error)
		{
			//If we cannot make indexes we are in a bad state and we need to start over
			postMessage('delete_all_client_side_dbs');
			throw new Error('Invalid state resetting databases');
		}
	
		try 
		{
			await db_items.get('_design/search');
		}
		catch (err) //Need to make the doc
		{
		    var ddoc = {
		      _id: '_design/search',
		      views: {
		       search: {
		        map: function(doc) {
		        const regex = /[\s\.;]+/gi;
		        ['name','item_number','product_id'].forEach(field => {
		          if (doc[field]) {
				  
					emit(doc[field].toLocaleLowerCase(), [field, doc[field]]);
				  
		            const words = doc[field].replaceAll(regex,
		              ',').split(',');
		            words.forEach(word => {
		              word = word.trim();
		              if (word.length) {
		                emit(word.toLocaleLowerCase(), [field, word]);
		              }
		            });
		          }
		        });
		       }.toString()
		      }
		      }
		    };
			try
			{
				await db_items.put(ddoc);
			}
			catch(error2)
			{
				//If we cannot make indexes we are in a bad state and we need to start over
				postMessage('delete_all_client_side_dbs');
				throw new Error('Invalid state resetting databases');
			}
		}


		db_settings.get('offline_item_offset',async function (error, doc) 
		{
			if (error) 
			{
				item_offset = 0;
				try
				{
					await db_settings.put({'_id':'offline_item_offset','value': item_offset });
				}
				catch(error2)
				{
					//If we cannot make indexes we are in a bad state and we need to start over
					postMessage('delete_all_client_side_dbs');
					throw new Error('Invalid state resetting databases');
				}
				var url = settings.site_url+'/sales/items_offline_data/'+item_limit+"/"+item_offset;
				ajax(url, {}, processItemAjax, 'POST');
			
			} 
			else 
			{
				var new_offline_item_offset = {'_id': 'offline_item_offset','value': (parseInt(doc.value))};
				new_offline_item_offset['_rev'] = doc._rev;
				try
				{
					await db_settings.put(new_offline_item_offset,{force: true});
				}
				catch(error2)
				{
					//If we cannot make indexes we are in a bad state and we need to start over
					postMessage('delete_all_client_side_dbs');
					throw new Error('Invalid state resetting databases');
				}
				var url = settings.site_url+'/sales/items_offline_data/'+item_limit+"/"+(parseInt(doc.value));
				ajax(url, {}, processItemAjax, 'POST');
			
			}
		});		
	
	
		async function processItemAjax(data) 
		{
			var items = JSON.parse(data);
	
			for(var k=0;k<items.length;k++)
			{
				var item = items[k];
				var new_item = {'_id': item.item_id+"_item",name:item.name,description:item.description,item_number: item.item_number, product_id:item.product_id,unit_price:item.unit_price,promo_price: item.promo_price,start_date:item.start_date,end_date:item.end_date,category:item.category,quantity:item.quantity,item_id:item.item_id,variations: item.variations,modifiers: item.modifiers, taxes: item.taxes, tax_included: item.tax_included};
				try
				{
					var doc = await db_items.get(items[k].item_id+"_item");
					new_item['_rev'] = doc._rev;
					await db_items.put(new_item,{force: true});
				}
				catch(error)
				{
					await db_items.put(new_item);
				}	
			}
		
		    await db_items.query('search', {
		      reduce: true
		    });
		
			db_settings.get('offline_item_offset',async function (error, doc) 
			{
				//Keep going
				if (items.length)
				{
					var new_offline_item_offset = {'_id': 'offline_item_offset','value': parseInt(doc.value)+item_limit};
					new_offline_item_offset['_rev'] = doc._rev;
					await db_settings.put(new_offline_item_offset,{force: true});
			
					var url = settings.site_url+'/sales/items_offline_data/'+item_limit+"/"+(parseInt(doc.value)+item_limit);
					ajax(url, {}, processItemAjax, 'POST');
				}
				else
				{
					var new_offline_item_offset = {'_id': 'offline_item_offset','value': 0};
					new_offline_item_offset['_rev'] = doc._rev;
					await db_settings.put(new_offline_item_offset,{force: true});
				
					db_settings.get('items_sync_last_run_time',async function (error2, doc2) 
					{	
						//Put the Date in we just ran so it don't run for a bit
						var new_items_sync_last_run_time = {'_id': 'items_sync_last_run_time','value': Date.now()};
						new_items_sync_last_run_time['_rev'] = doc2._rev;
						await db_settings.put(new_items_sync_last_run_time,{force: true});
					});	
				
				}
		
			});	
		
		}

	}
}
catch(exception_var)
{
	
}