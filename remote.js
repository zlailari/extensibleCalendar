/*!
 * Extensible 1.5.2
 * Copyright(c) 2010-2013 Extensible, LLC
 * licensing@ext.ensible.com
 * http://ext.ensible.com
 */
Ext.Loader.setConfig({
    enabled: true,
    disableCaching: false,
    paths: {
        "Extensible": "../../../src",

    }
});
Ext.require([
    'Ext.data.proxy.Rest',
    'Extensible.calendar.data.MemoryCalendarStore',
    'Extensible.calendar.data.EventStore',
    'Extensible.calendar.gadget.CalendarListPanel',
    'Extensible.calendar.CalendarPanel'
]);


//Ext.onReady(
loadCalendarData = function(){

    var calendarStore = Ext.create('Extensible.calendar.data.MemoryCalendarStore', {
        autoLoad: true,
        storeId: "Calendars",
        proxy: {
            type: 'rest',
            url: './backend/app.php/calendars',
            noCache: false,
            
            reader: {
                type: 'json',
                root: 'calendars'
            }
        }
    });
    
    var eventStore = Ext.create('Extensible.calendar.data.EventStore', {
        autoLoad: true,
        storeId: "Events",
        proxy: {
            type: 'rest',
       //     actionMethods: {create: 'POST', read: 'GET', update: 'PUT', destroy: 'DELETE'},
            url: 'backend/app.php/events',
            noCache: false,
            
            reader: {
                type: 'json',
                root: 'data'
            },
            
            writer: {
                type: 'json',
                nameProperty: 'mapping'
            },
            
            listeners: {
               

                exception: function(proxy, response, operation, options){
                    var msg = response.message ? response.message : Ext.decode(response.responseText).message;
                    // ideally an app would provide a less intrusive message display
                    Ext.Msg.alert('Server Error', msg);
                }
                

            }
        },

        // It's easy to provide generic CRUD messaging without having to handle events on every individual view.
        // Note that while the store provides individual add, update and remove events, those fire BEFORE the
        // remote transaction returns from the server -- they only signify that records were added to the store,
        // NOT that your changes were actually persisted correctly in the back end. The 'write' event is the best
        // option for generically messaging after CRUD persistence has succeeded.
        listeners: {
             load: {
                    fn: function() {
                        
                    }
                },
                
            'write': function(store, operation){
                var title = Ext.value(operation.records[0].data[Extensible.calendar.data.EventMappings.Title.name], '(No title)');
                switch(operation.action){
                    case 'create': 
                        Extensible.example.msg('Add', 'Added "' + title + '"');
                        Ext.getStore("Events").loadData([], false);
                        Ext.getStore("Events").load();
                        loadUserEventsListToDOM();
                        break;
                    case 'update':
                        Extensible.example.msg('Update', 'Updated "' + title + '"');
                        Ext.getStore("Events").loadData([], false);
                        Ext.getStore("Events").load();
                        loadUserEventsListToDOM();
                        break;
                    case 'destroy':
                        Extensible.example.msg('Delete', 'Deleted "' + title + '"');
                        Ext.getStore("Events").loadData([], false);
                        Ext.getStore("Events").load();
                        loadUserEventsListToDOM();
                        break;
                }
            }
        }
    });
    
    var cp = Ext.create('Extensible.calendar.CalendarPanel', {
        id: 'calendar-remote',
        eventStore: eventStore,
        calendarStore: calendarStore,
        renderTo: 'cal',
        title: 'Turgeman and Lailari Calendar',
        width: 900,
        height: 700
    });
    
    // You can optionally call load() here if you prefer instead of using the 
    // autoLoad config.  Note that as long as you call load AFTER the store
    // has been passed into the CalendarPanel the default start and end date parameters
    // will be set for you automatically (same thing with autoLoad:true).  However, if
    // you call load manually BEFORE the store has been passed into the CalendarPanel 
    // it will call the remote read method without any date parameters, which is most 
    // likely not what you'll want. 
    // store.load({ ... });
    
}
//);