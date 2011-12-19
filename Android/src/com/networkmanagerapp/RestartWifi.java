/*
   Copyright 2011 Robert Spencer

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
 */

package com.networkmanagerapp;

import java.io.IOException;
import java.net.URLEncoder;

import org.apache.http.HttpHost;
import org.apache.http.auth.AuthScope;
import org.apache.http.auth.UsernamePasswordCredentials;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.params.BasicHttpParams;
import org.apache.http.params.HttpConnectionParams;
import org.apache.http.params.HttpParams;

import android.app.IntentService;
import android.app.Notification;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.Intent;
import android.preference.PreferenceManager;
import android.util.Log;

/**
 * This class is a subclass of IntentService that restarts WIFI on the router in the background
 * @author rjs07u
 *
 */
public class RestartWifi extends IntentService {
	private NotificationManager mNM;

	/**
	 * Constructor calls the superclass constructor with a name. Used for debugging purposes
	 */
	public RestartWifi() {
		super("RestartWIFI");
	}
	
	/**
	 * Notifies the user of progress in the system notification area
	 */
	private void showNotification(){
		CharSequence text = getText(R.string.wifi_service_restarted);
		Notification notification = new Notification(R.drawable.ic_stat_networkman, text, System.currentTimeMillis());
		PendingIntent contentIntent = PendingIntent.getActivity(this, 0, new Intent(this, NetworkManagerMainActivity.class), 0);
		notification.setLatestEventInfo(this, text, text, contentIntent);
		mNM.notify(R.string.wifi_service_restarted, notification);
	}

	/**
	 * Requests the restart of WIFI in the background
	 * @param arg0 the data to process in the background
	 * @throws IOException caught locally. Catch throws NullPointerException, also caught internally.
	 */
	@Override
	protected void onHandleIntent(Intent arg0) {
		mNM = (NotificationManager)getSystemService(NOTIFICATION_SERVICE);
		showNotification();
		try{
			String password = PreferenceManager.getDefaultSharedPreferences(this).getString("password_preference", "");
			String ip = PreferenceManager.getDefaultSharedPreferences(this).getString("ip_preference", "192.168.1.1");
			String enc = URLEncoder.encode(ip, "utf-8");
			String scriptUrl = "http://" + enc + ":1080/cgi-bin/wifi.sh";
			HttpParams httpParams = new BasicHttpParams();
			// Set the timeout in milliseconds until a connection is established.
			int timeoutConnection = 3000;
			HttpConnectionParams.setConnectionTimeout(httpParams, timeoutConnection);
			// Set the default socket timeout (SO_TIMEOUT) 
			// in milliseconds which is the timeout for waiting for data.
			int timeoutSocket = 5000;
			HttpConnectionParams.setSoTimeout(httpParams, timeoutSocket);
			HttpHost targetHost = new HttpHost(enc, 1080, "http"); 
			DefaultHttpClient client = new DefaultHttpClient(httpParams);
			client.getCredentialsProvider().setCredentials(
			        new AuthScope(targetHost.getHostName(), targetHost.getPort()), 
			        new UsernamePasswordCredentials("root", password));
			HttpGet request = new HttpGet(scriptUrl);
			client.execute(targetHost, request);
		}catch(IOException ex){
			try{
				Log.e("Network Manager reboot", ex.getLocalizedMessage());
			}catch(NullPointerException e){
				Log.e("Network Manager reboot", "Rebooting, " + e.getLocalizedMessage());
			}
			
		}finally{
			mNM.cancel(R.string.wifi_service_restarted);
			stopSelf();
		}
	}

}
